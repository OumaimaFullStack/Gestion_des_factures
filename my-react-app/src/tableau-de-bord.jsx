import React, { useState, useEffect } from "react";
import { Bar, Pie } from "react-chartjs-2";
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, ArcElement, Tooltip, Legend } from "chart.js";
import './tableau-de-bord.css';

ChartJS.register(CategoryScale, LinearScale, BarElement, ArcElement, Tooltip, Legend);

const Dashboard = () => {
  const [stats, setStats] = useState({
    totalFactures: 0,
    montantTotal: 0,
    facturesPayees: 0,
    facturesImpayees: 0,
    facturesMensuelles: [], 
    chiffreAffairesMensuel: [] 
  });

  useEffect(() => {
    fetch("http://localhost:8000/statistique.php")
      .then((response) => response.json())
      .then((data) => {
        setStats({
          totalFactures: data.total_factures,
          montantTotal: data.montant_total,
          facturesPayees: data.factures_payes,
         facturesImpayees: data.factures_impayees,
          facturesMensuelles: data.factures_mensuelles,
          chiffreAffairesMensuel: data.chiffre_affaires_mensuel
        });
      })
      .catch((error) => console.error("Erreur lors de la récupération des données :", error));
  }, []);

  const barData = {
    labels: ["Jan", "Fév", "Mar", "Avr", "Mai", "Juin", "Juil", "Août", "Sep", "Oct", "Nov", "Dec"],
    datasets: [
      {
        label: "Nombre de factures par mois",
        data: stats.facturesMensuelles.map(item => item.nombre_factures),
        backgroundColor: "#214289"
      }
    ]
  };

  
  const revenueData = {
    labels: ["Jan", "Fév", "Mar", "Avr", "Mai", "Juin", "Juil", "Août", "Sep", "Oct", "Nov", "Dec"],
    datasets: [
      {
        label: "Chiffre d'affaires par mois",
        data: stats.chiffreAffairesMensuel.map(item => item.chiffre_affaires),
        backgroundColor: "#214289"
      }
    ]
  };

  const pieData = {
    labels: ["Factures payées", "Factures impayées"],
    datasets: [
      {
        data: [stats.facturesPayees, stats.facturesImpayees],
        backgroundColor: ["#3b82f6", "#a78bfa"]
      }
    ]
  };

  return (
    <div className="dashboard-container">
      <h2>Tableau de Bord</h2>
      <div className="stats-cards">
        <div className="stat-card">
          <h4>Total factures</h4>
          <p>{stats.totalFactures}</p>
        </div>
        <div className="stat-card">
          <h4>Montant Total</h4>
          <p>{stats.montantTotal} dh</p>
        </div>
        <div className="stat-card">
          <h4>Factures payées</h4>
          <p>{stats.facturesPayees}</p>
        </div>
        <div className="stat-card">
          <h4>Factures impayées</h4>
          <p>{stats.facturesImpayees}</p>
        </div>
      </div>
      <div className="charts-container">
        <div className="chart">
          <h3>Nombre de factures par mois</h3>
          <Bar data={barData} />
        </div>
        <div className="chart">
          <h3>Chiffre d'affaires par mois</h3>
          <Bar data={revenueData} />
        </div>
      </div>

      <div className="pie-chart">
        <h3>Répartition des factures</h3>
        <Pie data={pieData} />
      </div>
    </div>
  );
};

export default Dashboard;