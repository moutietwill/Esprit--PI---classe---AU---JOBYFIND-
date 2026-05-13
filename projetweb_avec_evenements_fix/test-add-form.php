<?php
/**
 * Quick test to verify the add event form works
 * Access via: http://localhost/projet/projetweb_avec_evenements_fix/test-add-form.php
 */

// Test basic form submission
echo "<!DOCTYPE html>
<html>
<head>
    <title>Test Add Event Form</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        form { border: 1px solid #ccc; padding: 20px; margin-top: 20px; }
        input, textarea { display: block; width: 100%; margin: 10px 0; padding: 8px; }
        button { padding: 10px 20px; background: #2d79ff; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Test Event Add Form</h1>
    
    <p>This page tests if the add event form works correctly.</p>
    
    <form method=\"POST\" action=\"/projet/projetweb_avec_evenements_fix/public/index.php/admin/storeEvent\" enctype=\"multipart/form-data\">
        <h2>Add Event</h2>
        <label>Title:</label>
        <input type=\"text\" name=\"titre\" value=\"Test Event\" required>
        
        <label>Description:</label>
        <textarea name=\"description\" required>This is a test event for verification purposes.</textarea>
        
        <label>Date:</label>
        <input type=\"text\" name=\"date\" value=\"" . date('Y-m-d') . "\" required>
        
        <label>Location:</label>
        <input type=\"text\" name=\"lieu\" value=\"Test Location\" required>
        
        <label>Organizer ID:</label>
        <input type=\"text\" name=\"idOrganisateur\" value=\"1\" required>
        
        <button type=\"submit\">Submit Test Event</button>
    </form>
    
    <p><a href=\"/projet/projetweb_avec_evenements_fix/public/index.php/admin/events\">Go back to events admin</a></p>
</body>
</html>";
?>
