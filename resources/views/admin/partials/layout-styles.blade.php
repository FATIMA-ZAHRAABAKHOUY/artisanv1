<style>
.admin-layout { display:flex; min-height:calc(100vh - 120px); }
.admin-sidebar {
    width:240px; flex-shrink:0; background:var(--indigo);
    min-height:100%; display:flex; flex-direction:column;
}
.admin-sidebar .section-lbl {
    font-size:10px; letter-spacing:1.5px; text-transform:uppercase;
    color:rgba(255,255,255,0.28); padding:16px 20px 5px; font-weight:600;
}
.admin-sidebar .nav-link {
    display:flex; align-items:center; gap:10px; padding:10px 20px;
    color:rgba(255,255,255,0.68); font-size:13.5px; text-decoration:none;
    transition:0.2s; border-left:3px solid transparent;
}
.admin-sidebar .nav-link:hover { color:white; background:rgba(255,255,255,0.06); }
.admin-sidebar .nav-link.active {
    color:var(--or-light); background:rgba(200,145,58,0.12); border-left-color:var(--or);
}
.admin-main { flex:1; background:var(--sable); padding:28px 32px; min-width:0; }
.admin-filter { background:white; border-radius:var(--radius); border:1px solid var(--sable-dark); padding:16px; margin-bottom:20px; }
.admin-filter label { font-size:12px; color:var(--gris-doux); display:block; margin-bottom:4px; }
@media (max-width:991px) {
    .admin-layout { flex-direction:column; }
    .admin-sidebar { width:100%; }
    .admin-main { padding:16px; }
}
</style>
