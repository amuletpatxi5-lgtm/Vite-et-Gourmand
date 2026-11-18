// =====================================================================
// Vite & Gourmand : statistiques de démonstration (MongoDB, US-13)
// Une copie par commande terminée de 06-avis-demo.sql (mêmes numéros, menus, montants, dates),
// =====================================================================
db = db.getSiblingDB('vite_gourmand_stats');

const commandes = [
    { numero_commande: 'VG-2025-0101', menu_id: 1, menu_titre: 'Menu de Noël', montant_centimes: 27200, date: new Date('2025-12-08T10:00:00+01:00') },
    { numero_commande: 'VG-2025-0102', menu_id: 2, menu_titre: 'Menu de Pâques', montant_centimes: 11000, date: new Date('2025-04-02T15:30:00+02:00') },
    { numero_commande: 'VG-2025-0103', menu_id: 4, menu_titre: 'Menu Événement', montant_centimes: 29000, date: new Date('2025-06-10T09:15:00+02:00') },
    { numero_commande: 'VG-2025-0104', menu_id: 3, menu_titre: 'Menu Classique', montant_centimes: 16800, date: new Date('2025-09-01T18:45:00+02:00') },
    { numero_commande: 'VG-2025-0105', menu_id: 5, menu_titre: 'Menu Végétarien', montant_centimes: 15000, date: new Date('2025-10-03T11:00:00+02:00') },
    { numero_commande: 'VG-2025-0106', menu_id: 6, menu_titre: 'Menu Végan', montant_centimes: 20000, date: new Date('2025-11-02T14:00:00+01:00') },
];

for (const c of commandes) {
    db.commandes.updateOne({ numero_commande: c.numero_commande }, { $set: c }, { upsert: true });
}
print('Commandes dans les statistiques : ' + db.commandes.countDocuments());
