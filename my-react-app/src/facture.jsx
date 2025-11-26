import { useState, useEffect } from "react";
import axios from "axios";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faTrash, faEdit, faFilePdf } from "@fortawesome/free-solid-svg-icons";
import "./facture.css";

const Facture = () => {
  const [facture, setFacture] = useState({
    nom_client: "",
    produits: "",
    quantites: "",
    date_echeance: "",
    etat: "",
    methode_paiement: "",
  });

  const [listFacture, setListFacture] = useState([]);
  const [listClients, setListClients] = useState([]);
  const [listProduits, setListProduits] = useState([]);
  const [editingFactNum, setEditingFactNum] = useState(null);
  const [filters, setFilters] = useState({ nom_client: "", date_echeance: "", date_creation: "", etat: "" });
  const [error, setError] = useState(null);

  useEffect(() => {
    fetchFactures();
    fetchClients();
    fetchProduits();
  }, []);

  const getAuthHeaders = () => {
    const token = localStorage.getItem("token");
    if (!token) {
      setError("Token non trouvé, veuillez vous reconnecter.");
      return {};
    }
    return { Authorization: `Bearer ${token}` };
  };

  const fetchFactures = async () => {
    try {
      setError(null);
      const response = await axios.get("http://localhost:8000/facture.php", {
        headers: getAuthHeaders(),
      });
      if (response.data.error) {
        setError(response.data.error);
        alert("Erreur API : " + response.data.error);
        return;
      }
      if (Array.isArray(response.data.factures)) {
        setListFacture(response.data.factures);
      } else {
        setError("La réponse n'est pas un tableau de factures");
      }
    } catch (error) {
      setError("Erreur lors de la récupération des factures");
      alert("Erreur lors de la récupération des factures.");
    }
  };

  const fetchClients = async () => {
    try {
      const response = await axios.get("http://localhost:8000/listeClient.php", {
        headers: getAuthHeaders(),
      });
      if (response.data.clients) {
        setListClients(response.data.clients);
      }
    } catch (error) {
      console.error("Erreur lors de la récupération des clients", error.response?.data || error);
    }
  };

  const fetchProduits = async () => {
    try {
      const response = await axios.get("http://localhost:8000/produit.php", {
        headers: getAuthHeaders(),
      });
      if (Array.isArray(response.data)) {
        setListProduits(response.data);
      }
    } catch (error) {
      console.error("Erreur lors de la récupération des produits", error.response?.data || error);
    }
  };

  const handleChange = (e) => {
    setFacture({
      ...facture,
      [e.target.name]: e.target.value,
    });
  };

  const ajouterOuModifier = async () => {
    if (facture.nom_client && facture.produits && facture.quantites && facture.date_echeance && facture.etat && facture.methode_paiement) {
      try {
        const isValidDate = (date) => !isNaN(new Date(date).getTime());
        if (!isValidDate(facture.date_echeance)) {
          alert("Veuillez fournir une date d'échéance valide !");
          return;
        }

        if (editingFactNum !== null) {
          const payload = {
            num_facture: editingFactNum,
            nom_client: facture.nom_client,
            date_echeance: facture.date_echeance,
            etat: facture.etat,
            methode_paiement: facture.methode_paiement,
            produits: [{ nom: facture.produits, quantite: facture.quantites }]
          };
          await axios.put("http://localhost:8000/facture.php", payload, {
            headers: getAuthHeaders(),
          });
          setEditingFactNum(null);
        } else {
          await axios.post("http://localhost:8000/facture.php", {
            nom_client: facture.nom_client,
            date_echeance: facture.date_echeance,
            etat: facture.etat,
            methode_paiement: facture.methode_paiement,
            produits: [{ nom: facture.produits, quantite: facture.quantites }]
          }, {
            headers: getAuthHeaders(),
          });
        }

        fetchFactures();
        setFacture({ nom_client: "", produits: "", quantites: "", date_echeance: "", etat: "", methode_paiement: "" });
      } catch (error) {
        alert("Erreur lors de l'ajout ou modification de la facture. Veuillez réessayer.");
      }
    } else {
      alert("Veuillez remplir tous les champs !");
    }
  };

  const supprimer = async (num_facture) => {
      if (!window.confirm("Voulez-vous vraiment supprimer cette facture ?")) return;
    try {
      await axios.delete(`http://localhost:8000/facture.php?num_facture=${num_facture}`, {
        headers: getAuthHeaders(),
        data: { num_facture: num_facture }
      
      });
      fetchFactures();
    } catch (error) {
      console.error("Erreur lors de la suppression de la facture", error.response?.data || error);
    }
  };

  const modifierFacture = (num_facture) => {
    const factureToEdit = listFacture.find((facture) => facture.num_facture === num_facture);
    if (factureToEdit) {
      setFacture(factureToEdit);
      setEditingFactNum(num_facture);
    }
  };

  const filteredFactures = listFacture.filter((facture) => {
    return (
      (filters.nom_client === "" || facture.nom_client.includes(filters.nom_client)) &&
      (filters.date_echeance === "" || facture.date_echeance === filters.date_echeance) &&
      (filters.date_creation === "" || facture.date_creation === filters.date_creation) &&
      (filters.etat === "" || facture.etat === filters.etat)
    );
  });

  const telechargerPDF = (num_facture) => {
    const token = localStorage.getItem("token");
    if (!token) {
      alert("Token manquant. Veuillez vous reconnecter.");
      return;
    }

    const url = `http://localhost:8000/pdf.php?num_facture=${num_facture}`;
    const link = document.createElement("a");
    link.href = url;
    link.setAttribute("download", `facture_${num_facture}.pdf`);
    link.setAttribute("target", "_blank");
    document.body.appendChild(link);
    link.click();
    link.remove();
  };

  return (
    <div className="facture-container">
      <h2>Simplifiez votre facturation!</h2>
      {error && <div className="error-message" style={{ color: "red", marginBottom: "10px" }}>{error}</div>}

      <form onSubmit={(e) => e.preventDefault()}>
        <div className="input4">
          <select name="nom_client" value={facture.nom_client} onChange={handleChange}>
            <option value="">Sélectionner un client</option>
            {listClients.map((client) => (
              <option key={client.id_client} value={client.nom}>
                {client.nom}
              </option>
            ))}
          </select>

          <select name="produits" value={facture.produits} onChange={handleChange}>
            <option value="">Sélectionner un produit</option>
            {listProduits.map((produit) => (
              <option key={produit.id} value={produit.id}>
                {produit.nom}
              </option>
            ))}
          </select>

          <input type="number" name="quantites" placeholder="Quantité" value={facture.quantites} onChange={handleChange} />
        </div>

        <div className="input5">
          <input type="date" name="date_echeance" value={facture.date_echeance} onChange={handleChange} />
          <select name="etat" value={facture.etat} onChange={handleChange}>
            <option value="">État</option>
            <option value="en attente">En attente</option>
            <option value="payé">Payé</option>
            <option value="impayé">Impayé</option>
          </select>
          <select name="methode_paiement" value={facture.methode_paiement} onChange={handleChange}>
            <option value="">Paiement</option>
            <option value="espèces">Espèces</option>
            <option value="virement">Virement</option>
            <option value="chèque">Chèque</option>
          </select>
        </div>

        <div className="facture-button">
          <button type="button" onClick={ajouterOuModifier}>
            {editingFactNum !== null ? "Modifier Facture" : "Créer Facture"}
          </button>
        </div>
      </form>

      <div className="liste-factures">
        <h2>Liste des Factures</h2>

        {/* FILTRES */}
        <div className="filtre-container" >

        
            <input
              type="text"
              placeholder="Nom du client"
              value={filters.nom_client}
              onChange={(e) => setFilters({ ...filters, nom_client: e.target.value })}
            />
            <input
              type="date"
              placeholder="Date d'échéance"
              value={filters.date_echeance}
              onChange={(e) => setFilters({ ...filters, date_echeance: e.target.value })}
            />
            <input
              type="date"
              placeholder="Date de création"
              value={filters.date_creation}
              onChange={(e) => setFilters({ ...filters, date_creation: e.target.value })}
            />
            <select
              value={filters.etat}
              onChange={(e) => setFilters({ ...filters, etat: e.target.value })}
            >
              <option value="">Tous les états</option>
              <option value="en attente">En attente</option>
              <option value="payé">Payé</option>
              <option value="impayé">Impayé</option>
            </select>
            <button className="filtre-button" onClick={() => setFilters({ nom_client: "", date_echeance: "", date_creation: "", etat: "" })}>
              Réinitialiser
            </button>
          </div>
        </div>

        <table border="1">
          <thead>
            <tr>
              <th>Numéro</th>
              <th>Client</th>
              <th>Produit</th>
              <th>Quantité</th>
              <th>Date d'échéance</th>
              <th>Date de création</th>
              <th>État</th>
              <th>Paiement</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            {filteredFactures.map((facture, index) => (
              <tr key={facture.num_facture ?? `facture-${index}`}>
                <td>{facture.num_facture}</td>
                <td>{facture.nom_client}</td>
                <td>{facture.produits}</td>
                <td>{facture.quantites}</td>
                <td>{facture.date_echeance}</td>
                <td>{facture.date_creation}</td>
                <td>{facture.etat}</td>
                <td>{facture.methode_paiement}</td>
                <td>
                  <button className="buttons"onClick={() => modifierFacture(facture.num_facture)}><FontAwesomeIcon icon={faEdit} /></button>
                  <button className="buttons"onClick={() => supprimer(facture.num_facture)}><FontAwesomeIcon icon={faTrash} /></button>
                  <button className="buttons"onClick={() => telechargerPDF(facture.num_facture)}><FontAwesomeIcon icon={faFilePdf} /></button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
    </div>
  );
};

export default Facture;
