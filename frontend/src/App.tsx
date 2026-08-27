import { useState } from 'react';
import { NavLink } from 'react-router-dom';
import { Bell, Bot, ChevronDown, LayoutDashboard, Menu, MessageSquare, Megaphone, Settings, Users, Workflow, X } from 'lucide-react';

const navigation = [
  { label: 'Dashboard', icon: LayoutDashboard },
  { label: 'Inbox', icon: MessageSquare, badge: '4' },
  { label: 'Contacts', icon: Users },
  { label: 'Campaigns', icon: Megaphone },
  { label: 'Automations', icon: Workflow },
  { label: 'AI Agents', icon: Bot },
];

export default function App() {
  const [mobileOpen, setMobileOpen] = useState(false);

  return (
    <div className="app-shell">
      <aside className={`sidebar ${mobileOpen ? 'sidebar-open' : ''}`}>
        <div className="brand"><span className="brand-mark">O</span><span>Omni<span className="brand-muted">SaaS</span></span></div>
        <div className="workspace-card"><div><small>WORKSPACE</small><strong>My Business</strong></div><ChevronDown size={16} /></div>
        <nav>{navigation.map(({ label, icon: Icon, badge }) => <NavLink key={label} to="#" className={({ isActive }) => `nav-item ${isActive && label === 'Dashboard' ? 'active' : ''}`}><Icon size={18}/><span>{label}</span>{badge && <b>{badge}</b>}</NavLink>)}</nav>
        <div className="sidebar-bottom"><NavLink to="#" className="nav-item"><Settings size={18}/><span>Settings</span></NavLink><div className="user-card"><div className="avatar">A</div><div><strong>Anish</strong><small>Owner</small></div><ChevronDown size={15}/></div></div>
      </aside>

      {mobileOpen && <button className="overlay" onClick={() => setMobileOpen(false)} aria-label="Close menu" />}
      <main className="main-content">
        <header className="topbar"><button className="mobile-menu" onClick={() => setMobileOpen(!mobileOpen)}>{mobileOpen ? <X/> : <Menu/>}</button><div><span className="eyebrow">OVERVIEW</span><h1>Dashboard</h1></div><div className="top-actions"><button className="icon-button"><Bell size={19}/></button><div className="date-chip">Thursday, Aug 27, 2026</div></div></header>
        <section className="content">
          <div className="welcome"><div><p className="eyebrow">GOOD AFTERNOON</p><h2>Welcome back, Anish.</h2><p>Here’s what’s happening across your customer channels today.</p></div><button className="primary">+ Create Campaign</button></div>
          <div className="metric-grid">{[['Total Contacts','0','—'],['Open Conversations','0','—'],['Active Campaigns','0','—'],['Automation Runs','0','Today']].map(([label,value,change]) => <div className="metric" key={label}><span>{label}</span><strong>{value}</strong><small>{change}</small></div>)}</div>
          <div className="panel-grid"><div className="panel"><div className="panel-head"><div><h3>Conversations</h3><p>Recent customer activity</p></div><button>View Inbox →</button></div><div className="empty-state"><div className="empty-icon"><MessageSquare size={22}/></div><strong>No conversations yet</strong><p>Connect WhatsApp or another channel to start receiving customer messages.</p></div></div><div className="panel"><div className="panel-head"><div><h3>Quick Start</h3><p>Get your workspace ready</p></div></div><div className="check-list"><div><span>01</span><p><strong>Connect WhatsApp</strong><small>Start receiving messages</small></p></div><div><span>02</span><p><strong>Import contacts</strong><small>Bring your customer list</small></p></div><div><span>03</span><p><strong>Create your first campaign</strong><small>Reach your audience</small></p></div></div></div></div>
        </section>
      </main>
    </div>
  );
}
