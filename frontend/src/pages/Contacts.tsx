import { useMemo, useState } from 'react';

type Contact = {
  id: number;
  name: string;
  email: string;
  phone: string;
  company: string;
  status: 'Active' | 'Inactive';
  source: string;
};

const seed: Contact[] = [
  { id: 1, name: 'Priya Sharma', email: 'priya@example.com', phone: '+91 98765 43210', company: 'Acme Studio', status: 'Active', source: 'WhatsApp' },
  { id: 2, name: 'Rahul Kumar', email: 'rahul@example.com', phone: '+91 98401 22334', company: 'Kumar & Co.', status: 'Active', source: 'Website' },
  { id: 3, name: 'Meera Nair', email: 'meera@example.com', phone: '+91 98950 11223', company: 'Nair Wellness', status: 'Inactive', source: 'Import' },
];

export default function Contacts() {
  const [contacts, setContacts] = useState(seed);
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('All');
  const [selected, setSelected] = useState<number[]>([]);
  const [showForm, setShowForm] = useState(false);
  const [newName, setNewName] = useState('');
  const [newEmail, setNewEmail] = useState('');

  const filtered = useMemo(() => contacts.filter(c => {
    const q = search.toLowerCase();
    const matchesSearch = !q || `${c.name} ${c.email} ${c.phone} ${c.company}`.toLowerCase().includes(q);
    const matchesStatus = status === 'All' || c.status === status;
    return matchesSearch && matchesStatus;
  }), [contacts, search, status]);

  const toggle = (id: number) => setSelected(s => s.includes(id) ? s.filter(x => x !== id) : [...s, id]);

  const addContact = () => {
    if (!newName.trim()) return;
    setContacts(c => [...c, { id: Date.now(), name: newName, email: newEmail, phone: '—', company: '—', status: 'Active', source: 'Manual' }]);
    setNewName(''); setNewEmail(''); setShowForm(false);
  };

  return (
    <main className="contacts-page">
      <div className="page-head">
        <div><p className="eyebrow">CRM</p><h1>Contacts</h1><p className="muted">Manage customers, leads and conversations from one place.</p></div>
        <div className="actions"><button className="secondary">Import</button><button className="primary" onClick={() => setShowForm(true)}>+ Add Contact</button></div>
      </div>

      <section className="toolbar card">
        <input aria-label="Search contacts" placeholder="Search contacts, phone, email or company…" value={search} onChange={e => setSearch(e.target.value)} />
        <select value={status} onChange={e => setStatus(e.target.value)}><option>All</option><option>Active</option><option>Inactive</option></select>
        {selected.length > 0 && <button className="secondary">Bulk actions ({selected.length})</button>}
      </section>

      <section className="card table-wrap">
        <div className="table-meta"><strong>{filtered.length} contacts</strong><span>Updated just now</span></div>
        <table><thead><tr><th></th><th>Contact</th><th>Company</th><th>Phone</th><th>Source</th><th>Status</th></tr></thead>
          <tbody>{filtered.map(c => <tr key={c.id} onClick={() => toggle(c.id)}><td><input type="checkbox" checked={selected.includes(c.id)} onChange={() => toggle(c.id)} onClick={e => e.stopPropagation()} /></td><td><div className="person"><span className="avatar">{c.name.split(' ').map(x => x[0]).join('')}</span><div><strong>{c.name}</strong><small>{c.email}</small></div></div></td><td>{c.company}</td><td>{c.phone}</td><td>{c.source}</td><td><span className={`status ${c.status.toLowerCase()}`}>{c.status}</span></td></tr>)}</tbody>
        </table>
        {filtered.length === 0 && <div className="empty">No contacts match your search.</div>}
      </section>

      {showForm && <div className="modal-backdrop"><div className="modal"><div className="modal-head"><h2>Add contact</h2><button onClick={() => setShowForm(false)}>×</button></div><label>Name<input value={newName} onChange={e => setNewName(e.target.value)} placeholder="Full name" /></label><label>Email<input value={newEmail} onChange={e => setNewEmail(e.target.value)} placeholder="name@company.com" /></label><div className="modal-actions"><button className="secondary" onClick={() => setShowForm(false)}>Cancel</button><button className="primary" onClick={addContact}>Create contact</button></div></div></div>}
    </main>
  );
}
