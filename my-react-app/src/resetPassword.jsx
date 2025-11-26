import React, { useState, useEffect } from "react";
import { useParams, useNavigate } from "react-router-dom";
import './resetPassword.css'

const ResetPassword = () => {
  const { token } = useParams(); 
  const navigate = useNavigate();
  const [password, setPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const [message, setMessage] = useState("");
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    console.log("Token récupéré :", token);
    if (!token) {
      setMessage("Lien invalide ou expiré.");
    }
  }, [token]);

 const handleSubmit = async (e) => {
  e.preventDefault();

  if (password !== confirmPassword) {
    setMessage("Les mots de passe ne correspondent pas.");
    return;
  }

  setLoading(true);
  setMessage("");

  try {
    const response = await fetch("http://localhost:3000/backend/reset_password.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams({
        token,
        password,
      }),
    });

    const text = await response.text();
    setMessage(text);

    if (text.includes("succès")) {
      setTimeout(() => navigate("/"), 3000); 
    }
  } catch (error) {
    setMessage("Erreur de connexion au serveur.");
  }

  setLoading(false);
};


  return (
    <div className="password-container">
      <h2>Reinitialiser le mot de passe</h2>
      {message && <p style={{ color: message.includes("succès") ? "green" : "red" }}>{message}</p>}

      {!message.includes("succès") && (
        <form onSubmit={handleSubmit}>
          <input
            type="password"
            placeholder="Nouveau mot de passe"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
          />
          <input
            type="password"
            placeholder="Confirmer le mot de passe"
            value={confirmPassword}
            onChange={(e) => setConfirmPassword(e.target.value)}
            required/>
          <button type="submit" disabled={loading}>
            {loading ? "Réinitialisation..." : "Réinitialiser"}
          </button>
        </form>
      )}
    </div>
  );
};

export default ResetPassword;
