<?php
class SupabaseClient {
    public $url;
    public $publishable;
    public $secret;

    function __construct() {
        $this->url = getenv("SUPABASE_URL");
        $this->publishable = getenv("SUPABASE_PUBLISHABLE_KEY");
        $this->secret = getenv("SUPABASE_SECRET_KEY");
    }

    // LOGIN (NUEVA API)
    function authLogin($email, $password) {

        $payload = json_encode([
            "email" => $email,
            "password" => $password
        ]);

        $curl = curl_init("{$this->url}/auth/v1/token?grant_type=password&redirect_to=");
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "apikey: {$this->publishable}",
            "Authorization: Bearer {$this->publishable}",
            "Content-Type: application/json"
        ]);

        $response = curl_exec($curl);
        return json_decode($response, true);
    }

    // CONSULTAS A BD (REST)
    function from($table, $method = "GET", $data = null, $filter = "") {
        $url = "{$this->url}/rest/v1/{$table}?{$filter}";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = [
            "apikey: {$this->publishable}",
            "Authorization: Bearer {$this->publishable}",
            "Content-Type: application/json",
            "Prefer: return=representation"
        ];

        if ($data !== null) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $res = curl_exec($curl);
        return json_decode($res, true);
    }
}

$supabase = new SupabaseClient();
