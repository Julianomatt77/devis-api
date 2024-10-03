<?php

namespace App\Service;

use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AnnuaireService
{
	private JWTTokenManagerInterface $jwtManager;
	private TokenStorageInterface $tokenStorageInterface;
	private UserRepository $userRepository;
	
	public function __construct(
        TokenStorageInterface $tokenStorageInterface,
        JWTTokenManagerInterface $jwtManager,
        UserRepository $userRepository
	) {
		$this->jwtManager = $jwtManager;
		$this->tokenStorageInterface = $tokenStorageInterface;
		$this->userRepository = $userRepository;
	}
	
	public function getUser(Request $request){
		// Décoder le token pour récupérer le user
		// Marche avec bearer Token
		$decodedJwtToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
		$username = $decodedJwtToken["username"];
		return $this->userRepository->findOneBy(['username' => $username]);
	}
	
	/**
	 * Decodes a formerly validated JWT token and returns the data it contains
	 * (payload / claims)
	 */
	public function decodeToken($token) {
		$parts = explode('.', $token);
		$payload = $parts[1];
		$payload = $this->urlsafeB64Decode($payload);
		$payload = json_decode($payload, true);
		
		return $payload;
	}
	
	/**
	 * Method compatible with "urlsafe" base64 encoding used by JWT lib
	 */
	public function urlsafeB64Decode($input) {
		$remainder = strlen($input) % 4;
		if ($remainder) {
			$padlen = 4 - $remainder;
			$input .= str_repeat('=', $padlen);
		}
		return base64_decode(strtr($input, '-_', '+/'));
	}
}