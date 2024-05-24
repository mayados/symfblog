<?php

namespace App\Services;

use DateTimeImmutable;

class JWTService
{
    //Generate token

    /*
     * @param array $header
     * @param array $payload
     * @param string $secret
     * @param int $validity
     * @return string
    */

    public function generate(array $header, array $payload, string $secret, int $validity = 10800): string
    {
        if($validity > 0){
            $now = new DateTimeImmutable();
            $exp = $now->getTimeStamp() + $validity;

            $payload['iat'] = $now->getTimestamp();
            $payload['exp'] = $exp;
        }

        //encode base 64
        $base64Header = base64_encode(json_encode($header));
        $base64Payload = base64_encode(json_encode($payload));

        // replace +, /, =
        $base64Header = str_replace(['+','/','='],['-','_',''],$base64Header);
        $base64Payload = str_replace(['+','/','='],['-','_',''],$base64Payload);

        //Generate signature
        $secret = base64_encode($secret);

        $signature = hash_hmac('sha256',$base64Header . '.' . $base64Payload, $secret, true);
        $base64Signature = base64_encode($signature);

        $signature = str_replace(['+','/','='],['-','_',''], $base64Signature);

        //create token 
        $jwt = $base64Header . '.' . $base64Payload. '.' .$signature;

        return $jwt;

    }

    //verify is the token is valid
    public function isValid(string $token): bool
    {
        return preg_match(
            '/^[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+$/',$token
        )=== 1;
    }

    // we get the payload
    public function getPayload(string $token): array
    {
        //explode
        $array = explode('.',$token);

        //decoding
        $payload = json_decode(base64_decode($array[1]), true);

        return $payload;
    }

    //get the header
    public function getHeader(string $token): array
    {
        $array = explode('.',$token);

        $header = json_decode(base64_decode($array[0]), true);

        return $header;
    }

    public function isExpired(string $token): bool
    {
        $payload = $this->getPayload($token);

        $now = new DateTimeImmutable();

        return $payload['exp'] < $now->getTimestamp();
    }

    //verifying token's signature
    public function check(string $token, string $secret)
    {
        $header = $this->getHeader($token);
        $payload = $this->getPayload($token);

        //generate token
        $verifToken = $this->generate($header, $payload, $secret, 0);

        return $token === $verifToken;
    }


}