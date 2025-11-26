import React, { useState } from "react";
import './passwordReset.css';
const PasswordResetRequest = () => {
  const [email, setEmail] = useState("");
  const [message, setMessage] = useState("");
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
  e.preventDefault();
  setLoading(true);
  setMessage("");

  try {
    const response = await fetch("http://localhost:3000/backend/forgot_password.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams({ email }),
    });

    const text = await response.text();
    setMessage(text);
  } catch (error) {
    setMessage("Erreur lors de la connexion au serveur.");
  }

  setLoading(false);
};


  return (
    <div className="password">
      <h2>Réinitialisation du mot de passe</h2>
      <p>Entrez votre email pour recevoir un lien de réinitialisation.</p>
      <form onSubmit={handleSubmit}>
        <input
          type="email"
          placeholder="Votre email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          required
        
        />
        <button type="submit" disabled={loading}>
          {loading ? "Envoi..." : "Envoyer le lien"}
        </button>
      </form>
      {message && <p style={{ color: "green" }}>{message}</p>}
    </div>
  );
};

export default PasswordResetRequest;
