<?php

namespace App\Controller;

use App\Entity\StructureParameters;
use App\Entity\User;
use App\Form\CreateStructureFormType;
use App\Repository\StructureParametersRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class StructuresController extends AbstractController
{

    public User $user;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->user = $tokenStorage->getToken()->getUser();
    }

    /**
     * @Route("/structures", name="structures")
     */
    public function index(UserRepository $userRepository, StructureParametersRepository $structureRepository): Response
    {

        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }
        if(!$this->toUser()->isForceChangePassword()){
            return $this->redirectToRoute('change_password');
        }
        if(!$this->isGranted('ROLE_STRUCTURE')){
            return $this->redirectToRoute('index');
        }
        if($this->toUser()->getState() == false){
            return $this->redirectToRoute('index');
        }
        if($this->isGranted('ROLE_ADMIN')){
            $structures = $userRepository->findByRole('ROLE_STRUCTURE');
        }else if($this->isGranted('ROLE_FRANCHISE')){
            $structures = $userRepository->findByIdList($structureRepository->findAllStructuresIDOfAnFranchise($this->user->getId()));
        }else if($this->isGranted('ROLE_STRUCTURE')){
            $structures = array($userRepository->findOneById($this->user->getId()));
        }

        $searchbar = $this->createFormBuilder() 
            ->add('mots', SearchType::class, [
                'label' => ' ',
                'attr' => [
                    'class' => 'searchbar',
                    'placeholder' => 'Rechercher une structure..'
                ]
            ])
            ->getForm()
            ;

        return $this->render('main/structures/structures.html.twig', [
            'structures' => $structures,
            'userRepository' => $userRepository,
            'structureRepository' => $structureRepository,
            'searchbar' => $searchbar->createView(),
        ]);
        
    }

    /**
     * @Route("/structures/edit/{id}", name="edit_structure")
     */
    public function edit(Request $request, StructureParametersRepository $structureRepository, $id, EntityManagerInterface $entityManager, UserRepository $userRepository, MailerInterface $mailer): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }
        if(!$this->toUser()->isForceChangePassword()){
            return $this->redirectToRoute('change_password');
        }

        if(!$this->isGranted('ROLE_ADMIN')){
            return $this->redirectToRoute('structures');
        }
        if($this->toUser()->getState() == false){
            return $this->redirectToRoute('index');
        }

        $structure = $structureRepository->findByStructureId($id);
        $user = $userRepository->findOneById($id);

        $form = $this->createFormBuilder() 
            ->add('sell_drinks', CheckboxType::class,        ['required' => false, 'attr' => ['checked' => $structure->isSellDrinks()]])
            ->add('manage_planning', CheckboxType::class,    ['required' => false, 'attr' => ['checked' => $structure->isManagePlanning()]])
            ->add('shop', CheckboxType::class,               ['required' => false, 'attr' => ['checked' => $structure->isShop()]])
            ->add('members_statistics', CheckboxType::class, ['required' => false, 'attr' => ['checked' => $structure->isMembersStatistics()]])
            ->add('payment_management', CheckboxType::class, ['required' => false, 'attr' => ['checked' => $structure->isPaymentManagement()]])
            ->add('state', CheckboxType::class, ['required' => false, 'attr' => ['checked' => $user->getState()]])
            ->add('submit', SubmitType::class, ['label' => 'Valider', 'attr' => ['class' => 'classic-btn']])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $structure->setSellDrinks       ($form->get('sell_drinks')->getData());
            $structure->setManagePlanning   ($form->get('manage_planning')->getData());
            $structure->setShop             ($form->get('shop')->getData());
            $structure->setMembersStatistics($form->get('members_statistics')->getData());
            $structure->setPaymentManagement($form->get('payment_management')->getData());
            $user->setState($form->get('state')->getData());

            $entityManager->persist($user);
            $entityManager->persist($structure);
            $entityManager->flush();

            $email = (new Email())
            ->from('contact@ecf.com')
            ->to($user->getEmail())
            ->subject("Modification des permissions")
            ->html('

            <p>Bonjour ' . $user->getName() . ', les permissions de votre structure ont été modifiées, vous pouvez allez voir ça sur votre plateforme en ligne</p>

            ');

            $mailer->send($email);

            return $this->redirectToRoute('structures');

        }

        return $this->render('main/structures/edit_structure.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/structures/create", name="create_structure")
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
            return $this->redirectToRoute('structures');
        }
        if($this->toUser()->getState() == false){
            return $this->redirectToRoute('index');
        }

        $form = $this->createForm(CreateStructureFormType::class);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $user = new User();
            $user->setEmail($form->get('email')->getData());
            $user->setName($form->get('name')->getData());
            $user->setRoles(array('ROLE_STRUCTURE'));
            $password = $this->randomPassword();
            $user->setPassword($userPasswordHasher->hashPassword($user, $password));
            $user->setAddress($form->get('address')->getData());
            $user->setState(true);
            $user->setForceChangePassword(false);

            
            $entityManager->persist($user);
            $entityManager->flush();

            $parameters = new StructureParameters();
            $parameters->setFranchiseId($form->get('franchise')->getData());
            $parameters->setStructureId($user->getId());
            $parameters->setSellDrinks(false);
            $parameters->setManagePlanning(false);
            $parameters->setShop(false);
            $parameters->setMembersStatistics(false);
            $parameters->setPaymentManagement(false);

            $entityManager->persist($parameters);
            $entityManager->flush();

            $email = (new Email())
            ->from('contact@ecf.com')
            ->to($form->get('email')->getData())
            ->subject("Confirmation d'inscription !")
            ->html('

            <p>Félicitations ' . $form->get('name')->getData() . ', vous êtes maintenant une structure de notre marque !</p>
            <p>Voici vos identifiants :</p>
            <p> - Email : ' . $form->get('email')->getData() . '</p>
            <p> - Mot de passe : ' . $password . '</p>

            ');

            $mailer->send($email);
            return $this->redirectToRoute('structures');

        }

        return $this->render('main/structures/create_structure.html.twig', [
            'form' => $form->createView(),
        ]);
    }

        /**
     * @Route("/structures/view/{id}", name="view_structure")
     */
    public function view(StructureParametersRepository $structureRepository, $id): Response
    {
        if(!$this->getUser()){
            return $this->redirectToRoute('app_login');
        }
        if(!$this->toUser()->isForceChangePassword()){
            return $this->redirectToRoute('change_password');
        }
        if($this->toUser()->getState() == false){
            return $this->redirectToRoute('index');
        }
        $structure = $structureRepository->findByStructureId($id);

        $infos = array();
        array_push($infos, $structure->isSellDrinks(), $structure->isManagePlanning(), $structure->isShop(), $structure->isMembersStatistics(), $structure->isPaymentManagement());

        $texts = array('Vendre des boissons', 'Gestion du planning', 'Boutique en ligne', 'Statistiques des membres', 'Gestion des païements');
        return $this->render('main/structures/view_structure.html.twig', [
            'infos' => $infos,
            'texts' => $texts
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
