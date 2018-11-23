<?php
namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\Date;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{

    protected $mailer;

    public function __Construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Matches /user/new exactly
     *
     * @Route("/", name="create_user")
     */
    public function create(Request $request)
    {
        $form = $this->getRegisterForm($request);

        return $this->render('user/new.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    private function getRegisterForm($request)
    {
        // creates a user
        $user = new User();
        // creates a form
        $form = $this->createFormBuilder($user)
            ->add('Name', TextType::class, array('label' => 'Nom'))
            ->add('Firstname', TextType::class, array('label' => 'Prénom'))
            ->add('Birth', BirthdayType::class, array('format' => 'd M y', 'label' => 'Date de naissance'))
            ->add('email', EmailType::class, array('label' => 'Email'))
            ->add('Sexe', ChoiceType::class,
                array('choices' => array(
                    'Femme' => 'femme',
                    'Homme' => 'homme'),
                    'multiple'=>false,'expanded'=>true,
                    'label' => 'Civilité'))
            ->add('Country', CountryType::class, array('label' => 'Pays'))
            ->add('Job', ChoiceType::class,
                array('choices' => array(
                    'Cadre' => 'cadre',
                    'Artisan' => 'artisan',
                    'Profession intermédiaire' => 'profession_intermediaire',
                    'Ouvrier' => 'ouvrier',
                    'Autre' => 'autre'),
                    'label' => 'Profession'))
            ->add('save', SubmitType::class, array('label' => 'Inscription'))
            ->setAction($this->generateUrl('create_post_user'))
            ->getForm();

        $form->handleRequest($request);
        return $form;
    }

    /**
     * Matches /user/create/post exactly
     *
     * @Route("/user/create/post", name="create_post_user")
     */
    public function createPost(Request $request)
    {
        $form = $this->getRegisterForm($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            $this->sendConfirmEmail($user);
            return $this->redirectToRoute('user');
        }

        return $this->redirectToRoute('create_user');
    }

    /**
     * Matches /user exactly
     *
     * @Route("/user", name="user")
     */
    public function index(Request $request) 
    {   
        return $this->render('user/confirmation.html.twig');
    }

    /**
     * Send emails to user and admin
     */
    public function sendConfirmEmail($user)
    {
        $user_message = (new \Swift_Message('Mail de confirmation utilisateur'))
            ->setFrom('jcanevet@siliconsalad.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView(
                    'email/registration.html.twig',
                    array('name' => $user->getFirstName())
                ),
                'text/html'
            );
        $admin_message = (new \Swift_Message('Mail de confirmation Admin'))
            ->setFrom('jcanevet@siliconsalad.com')
            ->setTo('jcanevet@siliconsalad.com')
            ->setBody(
                $this->renderView(
                    'email/registration-admin.html.twig',
                    array('name' => $user->getFirstName(),
                    'email' => $user->getEmail())
                ),
                'text/html'
            );
        

        $this->mailer->send($user_message);
        $this->mailer->send($admin_message);

        return $this->render('user/confirmation.html.twig');
    }
}
