<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\CreateFranchiseFormType;
use App\Repository\StructureParametersRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class FranchisesController extends AbstractController
{
    public User $user;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->user = $tokenStorage->getToken()->getUser();
    }

    /**
     * @Route("/franchises", name="franchises")
     */
    public function index(UserRepository $userRepository): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }
        if(!$this->toUser()->isForceChangePassword()){
            return $this->redirectToRoute('change_password');
        }
        if(!$this->isGranted('ROLE_FRANCHISE')){
            return $this->redirectToRoute('index');
        }
        if($this->toUser()->getState() == false){
            return $this->redirectToRoute('index');
        }
        if($this->isGranted('ROLE_ADMIN')){
            $franchises = $userRepository->findByRole('ROLE_FRANCHISE');
        }else{
            $franchises = array($userRepository->findOneById($this->user->getId()));
        }
        return $this->render('main/franchises/franchises.html.twig', [
            'franchises' => $franchises,
            ]);
    }

    /**
     * @Route("/franchises/create", name="create_franchise")
     */
    public function create(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }
        if(!$this->toUser()->isForceChangePassword()){
            return $this->redirectToRoute('change_password');
        }
        if(!$this->isGranted('ROLE_ADMIN')){
            return $this->redirectToRoute('franchises');
        }
        if($this->toUser()->getState() == false){
            return $this->redirectToRoute('index');
        }

        $form = $this->createForm(CreateFranchiseFormType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $user = new User();
            $user->setEmail($form->get('email')->getData());
            $user->setName($form->get('name')->getData());
            $user->setRoles(array('ROLE_FRANCHISE'));
            $password = $this->randomPassword();
            $user->setPassword($userPasswordHasher->hashPassword($user, $password));
            $user->setSite($form->get('site')->getData());
            $user->setState(true);
            $user->setForceChangePassword(false);


            $entityManager->persist($user);
            $entityManager->flush();

            $email = (new Email())
            ->from('contact@ecf.com')
            ->to($form->get('email')->getData())
            ->subject("Confirmation d'inscription !")
            ->html('

            <p>Félicitations ' . $form->get('name')->getData() . ', vous êtes maintenant une franchise de notre marque !</p>
            <p>Voici vos identifiants :</p>
            <p> - Email : ' . $form->get('email')->getData() . '</p>
            <p> - Mot de passe : ' . $password . '</p>

            ');

            $mailer->send($email);
            return $this->redirectToRoute('franchises');

        }

        return $this->render('main/franchises/create_franchise.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/franchises/edit/{id}", name="edit_franchise")
     */
    public function edit(Request $request, $id, UserRepository $userRepository, EntityManagerInterface $entityManager, StructureParametersRepository $structureRepository, MailerInterface $mailer): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }
        if(!$this->toUser()->isForceChangePassword()){
            return $this->redirectToRoute('change_password');
        }
        if(!$this->isGranted('ROLE_ADMIN')){
            return $this->redirectToRoute('franchises');
        }
        if($this->toUser()->getState() == false){
            return $this->redirectToRoute('index');
        }

        $franchise = $userRepository->findOneById($id);

        $form = $this->createFormBuilder() 
            ->add('name', TextType::class, ['attr' => ['value' => $franchise->getName(), 'class' => 'form-control','placeholder' => ' ',]])
            ->add('email', EmailType::class, ['attr' => ['value' => $franchise->getEmail(), 'class' => 'form-control','placeholder' => ' ',]])
            ->add('site', TextType::class, ['attr' => ['value' => $franchise->getSite(), 'class' => 'form-control','placeholder' => ' ',]])
            ->add('state', CheckboxType::class, ['required' => false, 'attr' => ['checked' => $franchise->getState()]])
            ->add('submit', SubmitType::class, ['label' => 'Valider', 'attr' => ['class' => 'classic-btn']])
            ->getForm();


        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $franchise->setName($form->get('name')->getData());
            $franchise->setEmail($form->get('email')->getData());
            $franchise->setSite($form->get('site')->getData());
            $franchise->setState($form->get('state')->getData());

            if($form->get('state')->getData() === false){
                $structures = $structureRepository->findByFranchiseId($id);
                foreach($structures as $structure){
                    $structureUser = $userRepository->findOneById($structure->getStructureId());
                    $structureUser->setState(false);
                    $entityManager->persist($structureUser);
                    $entityManager->flush();
                }
            }

            $entityManager->persist($franchise);
            $entityManager->flush();

            $email = (new Email())
            ->from('contact@ecf.com')
            ->to($franchise->getEmail())
            ->subject("Modification des permissions")
            ->html('

            <p>Bonjour ' . $form->get('name')->getData() . ', les permissions de votre franchise ont été modifiées, vous pouvez allez voir ça sur votre plateforme en ligne</p>

            ');

            $mailer->send($email);

            return $this->redirectToRoute('franchises');

        }

        return $this->render('main/franchises/edit_franchise.html.twig', [
            'form' => $form->createView()
        ]);
    }

    function randomPassword() {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890_@!=.-/*-#[]{}^$%?';
        $pass = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < 15; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass);
    }

    public function toUser(): ?User
    {
        return $this->getUser();
    }

}
