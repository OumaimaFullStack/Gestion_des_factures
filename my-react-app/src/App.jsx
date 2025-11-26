import { BrowserRouter as Router, Route, Routes, useLocation } from "react-router-dom";
import Connexion from "./connexion";
import Inscription from "./inscription";
import Accueil from "./Acceuil";
import Facture from "./facture";
import Client from "./client";
import Menu from "./menu";
import Header from "./header";
import PasswordResetRequest from "./passwordReset";
import ResetPassword from "./resetPassword";
import Dashboard from "./tableau-de-bord";

function Layout() {
  const location = useLocation();
  const hideHeaderRoutes = ["/"];
  const hideMenuRoutes = ["/", "/inscription", "/passwordReset"]; 

  return (
    <>
      {!hideHeaderRoutes.includes(location.pathname) && <Header />}
      {!hideMenuRoutes.includes(location.pathname) && !location.pathname.startsWith("/reset-password/") && <Menu />} 
      <Routes>

        <Route path="/reset-password/:token" element={<ResetPassword />} />
        <Route path="/passwordReset" element={<PasswordResetRequest />} />
        <Route path="/accueil" element={<Accueil />} />
        <Route path="/tableau-de-bord" element={<Dashboard />} />
        
        <Route path="/client" element={<Client />} />
        <Route path="/facture" element={<Facture />} />
        <Route path="/inscription" element={<Inscription />} />
        <Route path="/" element={<Connexion />} />
      </Routes>
    </>
  );
}

function App() {
  return (
    <Router>
      <Layout />
    </Router>
  );
}

export default App;
