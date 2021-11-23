<?php

namespace App\Controller;

use App\Repository\AdresseRepository;
use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ClientController
{
    private $clientRepository;
    private $adresseRepository;

    public function __construct(
        ClientRepository $clientRepository,
        AdresseRepository $adresseRepository
    ) {
        $this->clientRepository = $clientRepository;
        $this->adresseRepository = $adresseRepository;
    }

    /**
     * @Route("/client", name="get_all_client", methods={"GET"})
     */
    public function getAll($filter, $format = 'JSON'): JsonResponse
    {
        // TO DO check $filter
        // TO DO move to Services directory 
        $clients = $this->clientRepository->findBy($filter);
        $data = [];

        foreach ($clients as $client) {
            $dataClient = $client->toArray();
            $adresses = $dataClient['adresses']->getValues();

            $dataClient['clientAdresses'] = array_map(function ($adresse) {
                return $adresse->toArray();
            }, $adresses);

            unset($dataClient['adresses']);

            $data[] = $dataClient;
        }
        if ($format === 'CSV') {
           // TO DO
           // manage out in csv by using fputcsv()
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * @Route("/client/", name="add_client", methods={"POST"})
     */
    public function add(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $prenom = $data['prenom'];
        $nom = $data['nom'];
        $email = $data['email'];
        $adresses =  $data['adresses'];

        // TO DO move Check and Insert in Services directory 
        if (empty($prenom) || empty($email) || empty($nom) || !is_array($adresses) || count($adresses) < 1) {
            throw new NotFoundHttpException('Missing or wrong mandatory parameters!');
        }

        // The "email" field must be formatted as an email address 
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "Email address '$email' is considered invalid.\n";
        }

        // manage Client adresses 
        $createdAdresses = new ArrayCollection();
        $notCreatedAdresses = [];

        foreach ($adresses as $adresse) {
            // check adresse
            try {
                // TO DO Check for wrong adresses and give better feeds back
                if (empty($adresse['rue']) || empty($adresse['codePostal']) || empty($adresse['ville']) || empty($adresse['pays'])) {
                    $notCreatedAdresses[] = $adresse;
                    continue;
                }
                // check existance
                $found = $this->adresseRepository->findOneBy([
                    'rue' =>  $adresse['rue'],
                    'codePostal' =>  $adresse['codePostal'],
                    'ville' =>  $adresse['ville'],
                    'pays' =>  $adresse['pays']
                ]);

                if ($found) {
                    $adressToAdd = $found;
                } else {
                    // save adresse
                    $adressToAdd = $this->adresseRepository->saveAdresse(
                        $adresse['rue'],
                        $adresse['codePostal'],
                        $adresse['ville'],
                        $adresse['pays']
                    );
                }

                // add to the collection
                $createdAdresses->add($adressToAdd);
            } catch (\Throwable $th) {
                $notCreatedAdresses[] = $adresse;
            }
        }
        // save client
        try {
            $this->clientRepository->saveClient($prenom, $nom, $email, $notCreatedAdresses);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'Customer not created!',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse([
            'status' => 'Customer created!',
            'nbrAdressesCreated' => count($createdAdresses),
            'nbrAdressesNotCreated' => count($notCreatedAdresses)
        ], Response::HTTP_CREATED);
    }
}
