import React from "react";
import {Link} from 'react-router-dom';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faEnvelope, faPhone } from '@fortawesome/free-solid-svg-icons';
import './Acceuil.css';
const Accueil=()=>{
    return(
        <div className="accueil-container">
            <h2>Bienvenue sur MA.facture</h2>
            <img src="facture2.jpg" alt="Accueil" className="image"/>
            <p> Gagnez du temps et optimisez votre organisation avec un outil intuitif et efficace.</p>
            <div className="accueil-buttons">
                <button><Link to="/facture">Créer votre facture</Link></button>
                <button><Link to="/client">Gérer vos clients</Link></button>
            </div>
            <div className="contact">
                <p><FontAwesomeIcon icon={faEnvelope} />contact@MA.facture.ma</p>
                <p><FontAwesomeIcon icon={faPhone} />+212 6 XX XX XX XX</p>
            </div>
        </div>

    )
}
export default Accueil;
