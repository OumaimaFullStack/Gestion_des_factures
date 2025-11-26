import { useState } from "react";
import { useNavigate, Link } from "react-router-dom";
import "./connexion.css";

const Connexion = () => {
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [error, setError] = useState("");
    const navigate = useNavigate();

    const handleChange = (e) => {
        const { name, value } = e.target;
        if (name === "email") setEmail(value);
        else if (name === "password") setPassword(value);
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError(""); 

        try {
            const response = await fetch("http://localhost:8000/connexion.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    email: email,
                    mot_de_passe: password, 
                }),
            });

            const data = await response.json();

            if (response.ok) {
                localStorage.setItem("token", data.token);
                localStorage.setItem("role", data.role);
                navigate("/accueil"); 
            } else {
                setError(data.error || "Échec de la connexion");
            }
        } catch (error) {
            console.error("Erreur de connexion:", error);
            setError("Problème de connexion au serveur");
        }
    };

    return (
        <div className="connexion-container">
            <div className="left">
                <h3>MA.facture</h3>
                <h1 id="desc">Facture rapide, gestion efficace.<br />Accédez à votre espace maintenant</h1>
            </div>
            <div className="right">
                <h1 id="conn">CONNEXION</h1>
                {error && <p style={{ color: "red" }}>{error}</p>}
                <form onSubmit={handleSubmit}>
                    <input type="email" name="email" placeholder="Entrer votre email" value={email} onChange={handleChange} required /><br />
                    <input type="password" name="password" placeholder="Entrer votre mot de passe" value={password} onChange={handleChange} required /><br />
                    <Link to="/passwordReset">Mot de passe oublié</Link><br />
                    <button type="submit">Se connecter</button>
                    <p>Vous n'avez pas de compte ? <Link to="/inscription">S'inscrire</Link></p>
                </form>
            </div>
        </div>
    );
};

export default Connexion;
