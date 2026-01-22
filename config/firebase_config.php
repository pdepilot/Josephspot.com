<?php
/**
 * Firebase Analytics Configuration
 * 
 * To set up Firebase Analytics:
 * 1. Create a Firebase project at https://console.firebase.google.com
 * 2. Add a web app to your Firebase project
 * 3. Get your Firebase configuration object from Project Settings > General > Your apps
 * 4. Fill in the values below
 */

return [
    // Firebase Configuration
    'apiKey' => getenv('FIREBASE_API_KEY') ?: 'AIzaSyDHzRdN65PTWZkuEsNwh_CTGwP6viRuIY8',
    'authDomain' => getenv('FIREBASE_AUTH_DOMAIN') ?: 'josephspot-866e9.firebaseapp.com',
    'projectId' => getenv('FIREBASE_PROJECT_ID') ?: 'josephspot-866e9',
    'storageBucket' => getenv('FIREBASE_STORAGE_BUCKET') ?: 'josephspot-866e9.firebasestorage.app',
    'messagingSenderId' => getenv('FIREBASE_MESSAGING_SENDER_ID') ?: '66190413264',
    'appId' => getenv('FIREBASE_APP_ID') ?: '1:66190413264:web:60fe5eb46b5f7520cffdd5',
    'measurementId' => getenv('FIREBASE_MEASUREMENT_ID') ?: 'G-5HTDHQDV6S',
];
