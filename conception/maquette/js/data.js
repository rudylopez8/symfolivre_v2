/* ═══════════════════════════════════════════
   symfoLivre — Données de démonstration
   ═══════════════════════════════════════════ */

const BOOKS = [
  {
    id: 1,
    title: "Les Misérables",
    author: "Victor Hugo",
    isbn: "978-2253000330",
    category: "Roman",
    year: 1862,
    type: "TEXTE",
    fileFormat: ".txt",
    summary: "L'histoire de Jean Valjean, un ancien condamné des travaux forcés qui, après sa libération, lutte contre l'injustice sociale et cherche à se racheter. Roman magistral de la misère humaine, de la rédemption et de la lutte pour la justice, publié en 1862.",
    color: "#8B4513",
    icon: "📖"
  },
  {
    id: 2,
    title: "Le Gène égoïste",
    author: "Richard Dawkins",
    isbn: "978-2701126361",
    category: "Biologie",
    year: 1976,
    type: "TEXTE",
    fileFormat: ".md",
    summary: "Ouvre les portes de la biologie évolutive en proposant une vision centrée sur le gène comme unité fondamentale de la sélection naturelle. Dawkins démontre comment les gènes 'égoïstes' façonnent le comportement des organismes, la coopération, la méfiance et la culture humaine.",
    color: "#2D5016",
    icon: "🧬"
  },
  {
    id: 3,
    title: "La Cité des permutants",
    author: "Greg Egan",
    isbn: "978-2754800606",
    category: "Science-Fiction",
    year: 1994,
    type: "AUDIO",
    fileFormat: ".zip",
    summary: "Dans un univers où la physique quantique autorise la duplication et le repliement de l'espace-temps, un scientifique mène une enquête vertigineuse sur la nature de la réalité. Roman d'hard SF primé, une plongée intellectuelle au cœur des paradoxes de la physique moderne.",
    color: "#1a1a4e",
    icon: "🎧"
  },
  {
    id: 4,
    title: "Évolution",
    author: "Stephen Baxter",
    isbn: "978-2266197285",
    category: "Science-Fiction",
    year: 2002,
    type: "AUDIO",
    fileFormat: ".zip",
    summary: "Dans un futur où l'humanité a colonisé la Voie lactée, une équipe d'explorateurs découvre une race alien d'intelligence supérieure. Baxter propose une réflexion puissante sur la survie des espèces face à l'extinction et la conquête interstellaire.",
    color: "#2c1810",
    icon: "🎧"
  }
];

const CATEGORIES = ["Tous", "Roman", "Biologie", "Science-Fiction"];

const USERS_DEMO = [
  { id: 1, name: "Alice Martin", email: "alice@exemple.fr", role: "Admin", books: 0 },
  { id: 2, name: "Bob Dupont", email: "bob@exemple.fr", role: "Auteur", books: 2 },
  { id: 3, name: "Claire Petit", email: "claire@exemple.fr", role: "Lecteur", books: 0 },
  { id: 4, name: "David Leroy", email: "david@exemple.fr", role: "Lecteur", books: 0 },
];

// Baskets démo
const BASKET_DEMO = [1, 3]; // IDs des livres dans le panier