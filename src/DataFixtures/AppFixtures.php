<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use \DateTime;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Faker;
use App\Entity\Article;


class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {

        $faker = Faker\Factory::create('fr_FR');

        // Création d'un compte admin
        $admin = new User();

        // Hydratation du compte admin
        $admin
            ->setEmail('admin@a.a')
            ->setRegistrationDate( new DateTime('-1 year') )
            ->setPseudonym('Batman')
            ->setRoles(["ROLE_ADMIN"])
            ->setIsVerified(true)
            ->setPassword( $this->encoder->encodePassword($admin, 'Kikiki999+') )
        ;

        // persistance du compte admin
        $manager->persist($admin);

        // Création de 50 comptes utilisateurs
        for( $i = 0; $i < 50; $i++){
            // Création d'un compte admin
            $user = new User();

            // Hydratation du compte admin
            $user
                ->setEmail( $faker->email )
                ->setRegistrationDate( $faker->dateTimeBetween('-1 year', 'now') )
                ->setPseudonym($faker->userName)
                ->setIsVerified( $faker->boolean )
                ->setPassword( $this->encoder->encodePassword($user, 'Kikiki999+') )
            ;

            // persistance du compte user
            $manager->persist($user);
            }

        // Création de 200 articles
        for($i = 0; $i < 200; $i++){

            $article = new Article();

            $article
                ->setPublicationDate( $faker->dateTimeBetween($admin->getRegistrationDate(), 'now') )
                ->setAuthor($admin)
                ->setTitle( $faker->sentence(10) )
                ->setContent( $faker->paragraph(15) )
            ;

            // persistance du compte admin
            $manager->persist($article);

        }


        // validation des créations en BDD
        $manager->flush();
    }
}
