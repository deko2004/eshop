const products = [
  {
    id: 1,
    name: "Premium Headphones",
    price: 120.0,
    image: "imgs/headphone4.webp",
    category: "headphones",
    description:
      "High-quality wireless headphones with noise cancellation technology",
    features: [
      "Active Noise Cancellation",
      "40-hour battery life",
      "Premium sound quality",
      "Comfortable ear cushions",
    ],
    specifications: {
      brand: "TechPro",
      connectivity: "Bluetooth 5.0",
      batteryLife: "40 hours",
      weight: "250g",
    },
  },
  {
    id: 2,
    name: "Wireless Earbuds",
    price: 89.99,
    image: "imgs/airpod1.webp",
    category: "headphones",
    description: "True wireless earbuds with premium sound quality",
    features: [
      "True Wireless Technology",
      "24-hour battery life with case",
      "Touch controls",
      "Water resistant",
    ],
    specifications: {
      brand: "AudioPro",
      connectivity: "Bluetooth 5.1",
      batteryLife: "6 hours (24 with case)",
      weight: "5g per earbud",
    },
  },
  {
    id: 3,
    name: "Studio Headphones",
    price: 149.99,
    image: "imgs/headphone1.webp",
    category: "headphones",
    description: "Professional gaming headset with surround sound",
    features: [
      "7.1 Surround Sound",
      "Detachable microphone",
      "RGB lighting",
      "Ultra-comfortable design",
    ],
    specifications: {
      brand: "GamePro",
      connectivity: "USB / 3.5mm",
      surroundSound: "7.1 Virtual",
      weight: "320g",
    },
  },
  {
    id: 4,
    name: "Mechanical Keyboard",
    price: 129.99,
    image: "imgs/key1.png",
    category: "keyboards",
    description: "RGB mechanical gaming keyboard with custom switches",
    features: [
      "Mechanical switches",
      "Full RGB backlight",
      "Multimedia controls",
      "Anti-ghosting",
    ],
    specifications: {
      brand: "KeyMaster",
      switchType: "Blue mechanical",
      layout: "Full size",
      weight: "960g",
    },
  },
  {
    id: 5,
    name: "Gaming Keyboard",
    price: 109.99,
    image: "imgs/keyboard1.webp",
    category: "keyboards",
    description:
      "Professional gaming keyboard with customizable RGB lighting and macro keys",
    features: [
      "Full RGB customization",
      "Programmable macro keys",
      "Anti-ghosting technology",
      "Premium mechanical switches",
    ],
    specifications: {
      brand: "GameMaster",
      switchType: "Red mechanical",
      layout: "Full size with numpad",
      weight: "1.2kg",
    },
  },
  {
    id: 6,
    name: "Airpods 2",
    price: 39.99,
    image:
      "imgs/cd823ace-e5a4-4dbb-a98b-52425a09763d.006870179db6e5b7712d999447379a7b.webp",
    category: "headphones",
    description:
      "True wireless earbuds with excellent sound quality and comfortable fit",
    features: [
      "Wireless Bluetooth connection",
      "Touch controls",
      "Comfortable design",
      "Long battery life",
    ],
    specifications: {
      brand: "AudioPro",
      connectivity: "Bluetooth 5.0",
      batteryLife: "5 hours (24 with case)",
      weight: "4g per earbud",
    },
  },
  {
    id: 7,
    name: "Gaming Headset",
    price: 99.99,
    image: "imgs/head2.png",
    category: "headphones",
    description:
      "Professional gaming headset with virtual 7.1 surround sound and RGB lighting",
    features: [
      "Virtual 7.1 surround sound",
      "RGB lighting effects",
      "Detachable noise-canceling mic",
      "Memory foam ear cushions",
    ],
    specifications: {
      brand: "SoundPro",
      driver: "50mm neodymium",
      connection: "USB + 3.5mm",
      weight: "350g",
    },
  },
  {
    id: 8,
    name: "Gaming Laptop",
    price: 1299.99,
    image: "imgs/laptop1.webp",
    category: "laptops",
    description:
      "High-performance gaming laptop with RTX graphics and high refresh rate display",
    features: [
      "NVIDIA RTX 4060 Graphics",
      "16GB DDR5 RAM",
      "1TB NVMe SSD",
      "165Hz Display",
    ],
    specifications: {
      brand: "TechPro",
      processor: "Intel Core i7-13700H",
      display: '15.6" 2K 165Hz',
      weight: "2.1kg",
    },
  },
  {
    id: 9,
    name: "HOCO EW54 Noise‑Cancelling",
    price: 69.99,
    image: "imgs/headphone2.webp",
    category: "headphones",
    description:
      "Immerse yourself in pure sound with our HOCO EW54 noise‑cancelling headphones.",
    features: [
      "Active noise‑cancelling technology",
      "Wireless Bluetooth connectivity",
      "Lightweight design",
      "Comfortable ear pads",
    ],
    specifications: {
      brand: "HOCO",
      weight: "0.70 lbs",
    },
  },
  {
    id: 10,
    name: "HOCO EW54 Sports Headphones",
    price: 59.99,
    image: "imgs/boat203-1.png",
    category: "headphones",
    description:
      "Stay motivated during your workouts with our HOCO EW54 sports headphones.",
    features: [
      "Sweat‑resistant design",
      "Secure sports fit",
      "Wireless freedom",
      "Long battery life",
    ],
    specifications: {
      brand: "HOCO",
      weight: "0.65 lbs",
    },
  },
  {
    id: 11,
    name: "Sony Wireless Headphones",
    price: 39.99,
    image: "imgs/sony10.png",
    category: "headphones",
    description:
      "Experience immersive sound with our Sony wireless headphones.",
    features: [
      "High‑fidelity audio",
      "Wireless Bluetooth connection",
      "Over‑ear comfort",
      "Adjustable headband",
    ],
    specifications: {
      brand: "Sony",
      weight: "0.80 lbs",
    },
  },
  {
    id: 12,
    name: "Gaming Mouse",
    price: 129.99,
    image: "imgs/souris1.webp",
    category: "accessories",
    description:
      "Improve your gaming performance with our high‑quality gaming mouse.",
    features: [
      "Ergonomic design",
      "High‑precision optical sensor",
      "Programmable buttons",
      "Durable build",
    ],
    specifications: {
      brand: "Logitech",
      weight: "0.20 lbs",
    },
  },
  {
    id: 13,
    name: "MacBook Pro 16-inch",
    price: 2399.99,
    image: "imgs/laptop2.png",
    category: "laptops",
    description:
      "The MacBook Pro 16-inch delivers powerful performance with the Apple M2 Pro or M2 Max chip, a stunning Retina display, and long battery life.",
    features: [
      "Apple M2 Pro/Max chipset",
      "16‑inch Retina display",
      "Up to 21 hours battery life",
      "Thunderbolt 4 ports",
    ],
    specifications: {
      brand: "Apple",
      weight: "4.7 lbs",
    },
  },
];
