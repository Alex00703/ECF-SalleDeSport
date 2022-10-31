<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/")
     * @Route("/accueil", name="index")
     */
    public function index(): Response
    {
        $error = '';
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        if(!$this->toUser()->isForceChangePassword()){
            return $this->redirectToRoute('change_password');
        }

        if($this->toUser()->getState() == false){
            $error = "Votre compte est désactivé, vous n'avez accès qu'à cette page.";
        }

        return $this->render('main/index.html.twig', [
            'error' => $error,
        ]);
    }

    public function toUser(): ?User
    {
        return $this->getUser();
    }
}
