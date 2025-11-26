import React from "react";
import { Link } from "react-router-dom";
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'; 
import { faHome, faChartBar, faUsers, faFileInvoice } from '@fortawesome/free-solid-svg-icons'; 
import './menu.css';

const Menu = () => {
  return (
    <div className="menu">
        
      <li>
        <Link to="/accueil">
          <FontAwesomeIcon icon={faHome} /> Accueil
        </Link>
      </li>
      <li>
        <Link to="/tableau-de-bord">
          <FontAwesomeIcon icon={faChartBar} /> Tableau de Bord
        </Link>
      </li>
      <li>
        <Link to="/client">
          <FontAwesomeIcon icon={faUsers} /> Clients
        </Link>
      </li>
      <li>
        <Link to="/facture">
          <FontAwesomeIcon icon={faFileInvoice} /> Facture
        </Link>
      </li>
    
    </div>
  );
};

export default Menu;
