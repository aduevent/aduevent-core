<?php
// Check if the signature data is received
if (isset($_POST['signature'])) {
    // Get the signature data
    $signatureData = $_POST['signature'];

    // Remove the data prefix
    $signatureData = str_replace('data:image/png;base64,', '', $signatureData);

    // Decode the base64-encoded image data
    $signatureData = base64_decode($signatureData);

    // Generate a unique filename for the signature image
    $filename = 'signatures/' . uniqid('signature_') . '.png';

    // Save the signature image to the server
    if (file_put_contents($filename, $signatureData) !== false) {
        // Return success response
        http_response_code(200);
        echo json_encode(array("message" => "Signature saved successfully.", "filename" => $filename));
    } else {
        // Return error response
        http_response_code(500);
        echo json_encode(array("message" => "Failed to save signature."));
    }
} else {
    // Return error response if signature data is not received
    http_response_code(400);
    echo json_encode(array("message" => "Signature data not found."));
}
?>
