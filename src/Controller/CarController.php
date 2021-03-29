<?php

namespace App\Controller;

use App\Entity\Car;
use App\Form\Car1Type;
use App\Form\CarType;
use App\Repository\CarRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user/car")
 */
class CarController extends AbstractController
{
    /**
     * @Route("/", name="user_car_index", methods={"GET"})
     */
    public function index(CarRepository $carRepository): Response
    {
        $user = $this->getUser();//get login User data
        return $this->render('car/index.html.twig', [
            'cars' => $carRepository->findBy(['userid'=>$user->getId()]),
        ]);
    }

    /**
     * @Route("/new", name="user_car_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $car = new Car();
        $form = $this->createForm(Car1Type::class, $car);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $file = $request->files->get('car1')['image'];
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
                $car->setImage($newname);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($car);
                $entityManager->flush();

            }else{
                dump("Couldnt move");
                die();
            }
            $user = $this->getUser();//get login User data
            $car->setUserid($user->getId());
            $car->setStatus("New");
            $entityManager->persist($car);
            $entityManager->flush();

            return $this->redirectToRoute('user_car_index');
        }

        return $this->render('car/new.html.twig', [
            'car' => $car,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_car_show", methods={"GET"}, requirements={"id":"\d+"})
     */
    public function show(Car $car): Response
    {
        return $this->render('car/show.html.twig', [
            'car' => $car,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_car_edit", methods={"GET","POST"},requirements={"id":"\d+"})
     */
    public function edit(Request $request, Car $car): Response
    {
        $form = $this->createForm(Car1Type::class, $car);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $request->files->get('car1')['image'];
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
                $car->setImage($newname);
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($car);
            $entityManager->flush();


            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_car_index');
        }

        return $this->render('car/edit.html.twig', [
            'car' => $car,
            'form' => $form->createView(),
        ]);
    }
    /**
     * @return string
     */
    private function generateUniqueFileName(){
        //md5() reduces the similarity of the file names generated by
        //uniqid(), which is based on timestamps
        return  md5(uniqid());
    }
    /**
     * @Route("/{id}", name="user_car_delete", methods={"DELETE"}, requirements={"id":"\d+"})
     */
    public function delete(Request $request, Car $car): Response
    {
        if ($this->isCsrfTokenValid('delete'.$car->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($car);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_car_index');
    }
}
