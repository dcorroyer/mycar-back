<?php

namespace App\Service;

use App\Entity\Vehicule;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class VehiculeService
{
    /**
     * VehiculeController constructor.
     *
     * @param EntityManagerInterface $manager
     * @param SerializerInterface    $serializer
     * @param ValidatorInterface     $validator
     */
    public function __construct(
        private readonly EntityManagerInterface $manager,
        private readonly SerializerInterface    $serializer,
        private readonly ValidatorInterface     $validator,
    )
    {
    }

    /**
     * @param $vehicules
     * @param $groups
     * @return JsonResponse
     */
    public function findVehicules($vehicules, $groups): JsonResponse
    {
        return new JsonResponse(
            $this->serializer->serialize($vehicules, 'json', ['groups' => $groups]),
            Response::HTTP_OK,
            ['Content-type' => 'application/json'],
            true,
        );
    }

    /**
     * @return JsonResponse
     */
    public function notFoundVehicule(): JsonResponse
    {
        return new JsonResponse([
            'error' => "Cannot find Vehicule(s)"
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * @param Request $request
     * @param Vehicule|null $vehicule
     * @return JsonResponse
     */
    public function editVehicule(Request $request, ?Vehicule $vehicule): JsonResponse
    {
        $vehicule?->setUpdatedAt(new DateTime());

        $vehicule = $this->serializer->deserialize(
            $request->getContent(),
            Vehicule::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $vehicule] ?? []
        );

        $errors = $this->validator->validate($vehicule);

        if (count($errors) > 0) {
            return new JsonResponse([
                'error' => (string)$errors
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->manager->persist($vehicule);
        $this->manager->flush();

        return new JsonResponse(
            $this->serializer->serialize($vehicule, 'json', ['groups' => 'vehicule:write:item']),
            Response::HTTP_OK,
            ['Content-type' => 'application/json'],
            true,
        );
    }

    /**
     * @param Vehicule $vehicule
     * @return JsonResponse
     */
    public function deleteVehicule(Vehicule $vehicule): JsonResponse
    {
        $this->manager->remove($vehicule);
        $this->manager->flush();

        return new JsonResponse(
            $this->serializer->serialize($vehicule, 'json', ['groups' => 'vehicule:write:item']),
            Response::HTTP_OK,
            ['Content-type' => 'application/json'],
            true,
        );
    }
}