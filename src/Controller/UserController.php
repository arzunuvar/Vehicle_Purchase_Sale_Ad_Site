<?php

namespace App\Controller;

use App\Entity\Admin\Comment;
use App\Entity\User;
use App\Form\Admin\CommentType;
use App\Form\UserType;
use App\Repository\Admin\CommentRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('user/show.html.twig');
    }
    /**
     * @Route("/comments", name="user_comments", methods={"GET"})
     */
    public function comments(CommentRepository $commentRepository): Response
    {
        $user = $this->getUser();
//        echo $user->getId();
//        die();
       $comments= $commentRepository->getAllCommentsUser($user->getId());
//       dump($comments);
//        die();
        return $this->render('user/comments.html.twig',[
            'comments'=>$comments,
        ]);
    }
    /**
     * @Route("/cars", name="user_cars", methods={"GET"})
     */
    public function cars(): Response
    {
        return $this->render('user/cars.html.twig');
    }


    /**
     * @Route("/{id}", name="user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request,$id, User $user,UserPasswordEncoderInterface $passwordEncoder): Response
    {

            $user = $this->getUser();//get login User data
            if ($user->getId()!= $id){
                return $this->redirectToRoute('home');
            }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
// encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            //dump($user);
            $file = $request->files->get('user')['image'];
            if($file)
            {

                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $newname = uniqid() . $originalFilename .'.'.$file->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $file->move(
                        $this->getParameter('upload_directory'),
                        $newname
                    );
                } catch (FileException $e) {
                    dump("Something went wrong");
                    die();
                }
                $user->setImage($newname);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                return $this->redirectToRoute('user_index');
            }else{
                dump("Couldnt move");
                die();
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }


    /**
     * @Route("/newcomment/{id}", name="user_new_comment", methods={"GET","POST"})
     */
    public function newcomment(Request $request,$id): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);


        $submittedToken = $request->request->get('token');

        if ($form->isSubmitted()) {
            if($this->isCsrfTokenValid('comment',$submittedToken)) {
                $entityManager = $this->getDoctrine()->getManager();

                $comment->setStatus('New');
                $comment->setIp($_SERVER['REMOTE_ADDR']);
                $comment->setCarid($id);
                $user = $this->getUser();//get login User data
                $comment->setUserid($user->getId());

                $entityManager->persist($comment);
                $entityManager->flush();


                $this->addFlash('success', 'Yorumunuz başarıyla gönderilmiştir');
                return $this->redirectToRoute('car_show', ['id' => $id]);
            }
        }

        return $this->redirectToRoute('car_show',['id'=>$id]);

    }
}
