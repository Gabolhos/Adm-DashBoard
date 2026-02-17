// Firebase Configuration
import { initializeApp } from "https://www.gstatic.com/firebasejs/10.9.0/firebase-app.js";
import { getAuth } from "https://www.gstatic.com/firebasejs/10.9.0/firebase-auth.js";

const firebaseConfig = {
    apiKey: "AIzaSyC642PXZVjV96ORWO3qMcuEYe0lIMdIE9Q",
    authDomain: "mec-projeto-65861.firebaseapp.com",
    projectId: "mec-projeto-65861",
    storageBucket: "mec-projeto-65861.firebasestorage.app",
    messagingSenderId: "625687541988",
    appId: "1:625687541988:web:fc82f6cb314ecc380c14f1"
};

const app = initializeApp(firebaseConfig);
const auth = getAuth(app);

export { auth };
