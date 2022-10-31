<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AuthController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('index');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/changePassword", name="change_password")
     */
    public function changePassword(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        if($this->getUserr()->isForceChangePassword()){
            return $this->redirectToRoute('index');
        }

        $form = $this->createFormBuilder() 
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent correspondre.',
                'first_options'  => ['label' => 'Mot de passe', 'attr' => ['placeholder'=> ' ', 'class' => 'form-control']],
                'second_options' => ['label' => 'Confirmation', 'attr' => ['placeholder'=> ' ', 'class' => 'form-control']]
            ])
            ->getForm();

        $form->handleRequest($request);


        if($form->isSubmitted() && $form->isValid()){
            $user = $this->getUserr();
            $password = $form->get('password')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $password));
            $user->setForceChangePassword(true);

            $entityManager->persist($user);
            $entityManager->flush();

            $email = (new Email())
            ->from('contact@ecf.com')
            ->to($user->getEmail())
            ->subject("Changement du mot de passe")
            ->html('

            <p>Bonjour ' . $user->getName() . ', votre mot de passe a été modifié avec succès !</p>

            ');

            $mailer->send($email);

            return $this->redirectToRoute('index');
        }

        return $this->render('main/change_password.html.twig', [
            'form' => $form->createView()
        ]);
    }


    public function getUserr(): ?User
    {
        return $this->getUser();
    }
}
