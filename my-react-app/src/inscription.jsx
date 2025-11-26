import { useState } from "react";
import { useNavigate } from "react-router-dom";
import './inscription.css';

const Inscription = () => {
  const [form, setForm] = useState({
    nom: "",
    prenom: "",
    email: "",
    mot_de_passe: "",
    role: "",
  });

  const [error, setError] = useState(null); 
  const [message, setMessage] = useState(null); 
  const navigate = useNavigate();

  const handleChange = (e) => {
    setForm({
      ...form,
      [e.target.name]: e.target.value,
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!form.role) {
      setError("Veuillez choisir un rôle.");
      return;
    }
    try {
      const response = await fetch('http://localhost:8000/inscrp.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(form),
      });

      const result = await response.json();

      if (response.ok) {
        setMessage(result.message);
        setError(null); 
        navigate("/"); 
      } else {
        setError(result.error);
        setMessage(null); 
      }
    } catch (error) {
      setError("Erreur lors de la soumission du formulaire");
      setMessage(null); 
    }
  };

  return (
    <div className="global">
      <h3>MA.facture</h3>

      <div className="inscription-container">
        <h1>Inscription</h1>
        {error && <p className="error">{error}</p>}
        {message && <p className="success">{message}</p>} 
        <form onSubmit={handleSubmit}>
          <input type="text"name="nom" placeholder="Entrer votre nom"value={form.nom}onChange={handleChange}required/>
          <input type="text"name="prenom"placeholder="Entrer votre prénom"value={form.prenom} onChange={handleChange} required/>
          <input type="email"name="email"placeholder="Entrer votre email"value={form.email}onChange={handleChange}required/>
          <input type="password" name="mot_de_passe" placeholder="Entrer votre mot de passe" value={form.mot_de_passe} onChange={handleChange} required/>
          <select name="role"value={form.role}onChange={handleChange}required>
            <option value="">Choisir un rôle</option>
            <option value="Administrateur">Administrateur</option>
            <option value="utilisateur standard">Utilisateur standard</option>
          </select>
          <button type="submit">Valider</button>
        </form>
      </div>
    </div>
  );
};

export default Inscription;
