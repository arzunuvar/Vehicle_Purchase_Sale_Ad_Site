<?php

namespace App\Controller\Admin;

use App\Entity\Image;
use App\Entity\Car;
use App\Form\ImageType;
use App\Repository\ImageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/image")
 */
class ImageController extends AbstractController
{
    /**
     * @Route("/", name="admin_image_index", methods={"GET"})
     */
    public function index(ImageRepository $imageRepository): Response
    {
        return $this->render('admin/image/index.html.twig', [
            'images' => $imageRepository->findAll(),
        ]);
    }


    /**
     * @Route("/new/{id}", name="admin_image_new_for_car", methods={"GET","POST"} , requirements={"id":"\d+"})
     * @param Request $request
     * @param Car $car
     * @param ImageRepository $imageRepository
     * @return Response
     */
    public function newForCar(Request $request,Car $car,ImageRepository $imageRepository): Response
    {
        $image = new Image();
        $id = $car->getId();
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            //****************file upload**>>>>>>>>>>>>>>>>>>>>>>
            /** @var file  $file */
            $file=$request->files->get('image')['image'];
            if($file){
                $fileName=uniqid() . '.' . $file->guessExtension();
                //Move the file to the directory where brochures are stored
                try{
                    $file->move(
                        $this->getParameter('upload_directory'),//in Servis.yaml defined for upload images
                        $fileName
                    );
                }catch (FileException $e){
                    // ... handle exeption if something happens during file upload
                }
                $image->setImage($fileName);//Related upload file name with car table image field
            }
            //<<<<<<<<<<<<<<<<<<*****************file upload *************>

            $entityManager->persist($image);
            $entityManager->flush();

            return $this->redirectToRoute('admin_image_new_for_car',['id'=>$id]);//admin_image_index
        }
        $images =$imageRepository->findAll();

        return $this->render('admin/image/new_for_car.html.twig', [
            'image' => $image,
            'id'=>$id,
            'images'=>$images,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/new/", name="admin_image_new", methods={"GET","POST"})
     * @param Request $request
     * @param Car $car
     * @param ImageRepository $imageRepository
     * @return Response
     */
    public function new(Request $request, ImageRepository $imageRepository): Response
    {
        $image = new Image();
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $entityManager = $this->getDoctrine()->getManager();
            //****************file upload**>>>>>>>>>>>>>>>>>>>>>>
            /** @var file  $file */
            $file=$request->files->get('image')['image'];
            if($file){
                $fileName=uniqid() . '.' . $file->guessExtension();
                //Move the file to the directory where brochures are stored
                try{
                    $file->move(
                        $this->getParameter('upload_directory'),//in Servis.yaml defined for upload images
                        $fileName
                    );
                }catch (FileException $e){
                    // ... handle exeption if something happens during file upload
                }
                $image->setImage($fileName);//Related upload file name with car table image field
            }
            //<<<<<<<<<<<<<<<<<<*****************file upload *************>

            $entityManager->persist($image);
            $entityManager->flush();

            return $this->redirectToRoute('admin_image_new');//admin_image_index
        }
        $images =$imageRepository->findAll();

        return $this->render('admin/image/new.html.twig', [
            'image' => $image,
            'images'=>$images,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @return string
     */

    /**
     * @Route("/{id}", name="admin_image_show", methods={"GET"} , requirements={"id":"\d+"})
     */
    public function show(Image $image): Response
    {
        return $this->render('admin/image/show.html.twig', [
            'image' => $image,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_image_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Image $image): Response
    {
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_image_index');
        }

        return $this->render('admin/image/edit.html.twig', [
            'image' => $image,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_image_delete", methods={"DELETE"}, requirements={"id":"\d+"})
     */
    public function delete(Request $request, Image $image): Response
    {
        if ($this->isCsrfTokenValid('delete'.$image->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($image);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_image_index');
    }
    /**
     * @Route("/{id}", name="admin_car_image_delete", methods={"DELETE"})
     */
    public function deletecar(Request $request, Image $image): Response
    {
        if ($this->isCsrfTokenValid('delete'.$image->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($image);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_image_new_for_car');
    }
}
