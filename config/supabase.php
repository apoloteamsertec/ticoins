<?php
class SupabaseClient {
    public $url;
    public $anon_key;
    public $service_key;

    function __construct() {
        $this->url = getenv("https://uuzufvjfycvwznyzsprp.supabase.co");
        $this->anon_key = getenv("eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InV1enVmdmpmeWN2d3pueXpzcHJwIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjM4Mzk5MTMsImV4cCI6MjA3OTQxNTkxM30.yaCtI498PbL0y_dz9-fnWuUhWItFxUZ3fXzKKCdhhgo");
        $this->service_key = getenv("eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InV1enVmdmpmeWN2d3pueXpzcHJwIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc2MzgzOTkxMywiZXhwIjoyMDc5NDE1OTEzfQ.RL60LkxJjQz2ESB_88tqjaiiE8GmG3SFrtWBjcH0xgw");
    }

    function authLogin($email, $password) {
        $payload = json_encode([
            "email" => $email,
            "password" => $password
        ]);

        $curl = curl_init("{$this->url}/auth/v1/token?grant_type=password");
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "apikey: {$this->anon_key}",
            "Content-Type: application/json"
        ]);

        return json_decode(curl_exec($curl), true);
    }

    function from($table, $method = "GET", $data = null, $filter = "") {
        $url = "{$this->url}/rest/v1/$table?$filter";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = [
            "apikey: {$this->service_key}",
            "Authorization: Bearer {$this->service_key}",
            "Content-Type: application/json"
        ];

        if ($data !== null) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        return json_decode(curl_exec($curl), true);
    }
}

$supabase = new SupabaseClient();
