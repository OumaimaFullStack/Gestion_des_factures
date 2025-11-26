import { useState, useEffect } from "react";
import axios from "axios";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faTrash, faEdit } from "@fortawesome/free-solid-svg-icons";
import "./client.css";

const Client = () => {
  const [client, setClient] = useState({
    id_client: null,
    nom: "",
    prenom: "",
    email: "",
    telephone: "",
    adresse: "",
  });

  const [listClient, setListClient] = useState([]);
  const [editingClientId, setEditingClientId] = useState(null);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    fetchClients();
  }, []);

  const fetchClients = async () => {
    try {
      const jwtToken = localStorage.getItem("token");
      if (!jwtToken) throw new Error("Aucun token JWT trouvé !");

      const response = await axios.get("http://localhost:8000/recupClient.php", {
        headers: { Authorization: `Bearer ${jwtToken}` },
      });
      console.log("Données reçues:", response.data);
      setListClient(response.data);
    } catch (error) {
      console.error("Erreur lors de la récupération des clients:", error);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    editingClientId ? await modifierClient() : await ajouterClient();
  };

  const ajouterClient = async () => {
    try {
      setLoading(true);
      const response = await axios.post("http://localhost:8000/ajtClient.php", client, {
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
      });

      alert(response.data.success || response.data.error);
      fetchClients();
      resetClientForm();
    } catch (error) {
      console.error("Erreur lors de l'ajout du client:", error);
    } finally {
      setLoading(false);
    }
  };

  
  const modifierClient = async () => {
    try {
      setLoading(true);
      const response = await axios.post("http://localhost:8000/modifier_client.php", client, {
        headers: { Authorization: `Bearer ${localStorage.getItem("token")}` },
      });
    alert(response.data.success || response.data.error);
      fetchClients();
      resetClientForm();
      setEditingClientId(null);
    } catch (error) {
      console.error("Erreur lors de la mise à jour du client:", error);
    } finally {
      setLoading(false);
    }
  };

  const supprimerClient = async (id_client) => {
    if (!window.confirm("Voulez-vous vraiment supprimer ce client ?")) return;

    try {
      setLoading(true);
      const response = await axios.post("http://localhost:8000/suppClient.php", { id_client }, {
        headers: { Authorization: `Bearer ${localStorage.getItem("token")}` },
      });

      if (response.data.status="success") {
        alert(response.data.message);
      } else {
        alert(response.data.message || "Une erreur est survenue.");
      }

      fetchClients();
    } catch (error) {
      console.error("Erreur lors de la suppression du client:", error);
    } finally {
      setLoading(false);
    }
  };

  const remplirFormulaireModification = (clientData) => {
    setClient({ ...clientData });
    setEditingClientId(clientData.id_client);
  };

  const resetClientForm = () => {
    setClient({ id_client: null, nom: "", prenom: "", email: "", telephone: "", adresse: "" });
  };

  return (
    <div className="client-container">
      <h2>Gérez et Suivez Vos Clients Facilement</h2>
      <form onSubmit={handleSubmit}>
        <div className="input1">
          <input type="text" name="nom" placeholder="Nom" value={client.nom} onChange={(e) => setClient({ ...client, nom: e.target.value })} />
          <input type="text" name="prenom" placeholder="Prénom" value={client.prenom} onChange={(e) => setClient({ ...client, prenom: e.target.value })} />
        </div>
        <div className="input2">
          <input type="email" name="email" placeholder="Email" value={client.email} onChange={(e) => setClient({ ...client, email: e.target.value })} />
          <input type="text" name="telephone" placeholder="Téléphone" value={client.telephone} onChange={(e) => setClient({ ...client, telephone: e.target.value })} />
        </div>
        <div className="input3">
          <input type="text" name="adresse" placeholder="Adresse" value={client.adresse} onChange={(e) => setClient({ ...client, adresse: e.target.value })} />
        </div>
        <button type="submit" className="button-container" disabled={loading}>
          {loading ? "En cours..." : editingClientId ? "Modifier" : "Ajouter"}
        </button>
      </form>

      <div className="liste">
        <h2>Liste des Clients</h2>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Nom</th>
              <th>Prénom</th>
              <th>Email</th>
              <th>Téléphone</th>
              <th>Adresse</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            {listClient.map((client) => (
              <tr key={client.id_client}>
                <td>{client.id_client}</td>
                <td>{client.nom}</td>
                <td>{client.prenom}</td>
                <td>{client.email}</td>
                <td>{client.telephone}</td>
                <td>{client.adresse}</td>
                <td>
                  <button onClick={() => remplirFormulaireModification(client)}>
                    <FontAwesomeIcon icon={faEdit} />
                  </button>
                  <button onClick={() => supprimerClient(client.id_client)}>
                    <FontAwesomeIcon icon={faTrash} />
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
};

export default Client;
