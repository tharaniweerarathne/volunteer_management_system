<?php

class PredictionService {
    public function predictParticipation($userFeatures){
        $url = "http://127.0.0.1:5000/predict_participation";

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json"
        ]);
        
        // Add timeout to prevent hanging
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        // Debug: log the data being sent
        $jsonData = json_encode($userFeatures);
        error_log("Sending to Flask: " . $jsonData);
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

        $response = curl_exec($ch);
        
        // Debug: check for curl errors
        if (curl_error($ch)) {
            error_log("Curl error: " . curl_error($ch));
        }
        
        // Debug: log the response
        error_log("Flask response: " . $response);

        curl_close($ch);

        return json_decode($response, true);
    }
}
?>