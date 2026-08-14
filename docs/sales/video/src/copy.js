// All on-screen text, one object per language.
//
// Accuracy rule (same as the sales deck and handbook): every claim here is
// backed by something that actually exists in the product. No online payment,
// no coupons, no multi-branch, and "three languages" — not fourteen.

const en = {
  dir: 'ltr',
  intro: {
    kicker: 'THE OPERATING SYSTEM FOR CAR RENTAL',
    title: ['Run your entire', 'rental agency', 'from one desk.'],
    accentLine: 2,
  },
  problem: {
    kicker: 'Today',
    lines: ['A whiteboard.', 'An Excel file.', 'A WhatsApp group.', 'A Word contract.'],
    punch: ['It works —', 'until it doesn’t.'],
  },
  dashboard: {
    kicker: 'Every morning',
    title: 'The whole business,\nin five seconds.',
    callouts: ['Cars out today', 'Returns due', 'Maintenance due', 'Revenue today'],
  },
  planning: {
    kicker: 'Planning',
    title: 'Replace the whiteboard.',
    sub: 'One row per vehicle. One bar per rental.\nEvery gap is a car you could be renting.',
  },
  fleet: {
    kicker: 'Fleet & customers',
    title: 'Every car has a file.\nSo does every customer.',
    left: 'Plate, papers, rate,\nservice history',
    right: 'Licence, ID scans,\nand a blacklist',
  },
  contract: {
    kicker: 'Contracts',
    title: 'Generated, branded,\nsigned on screen.',
    bullets: ['Built from the booking', 'Your legal identifiers', 'Your terms', 'Signed with a finger'],
  },
  money: {
    kicker: 'Money',
    title: 'Priced, taxed, invoiced —\nautomatically.',
    chips: ['HT', 'TVA 20%', 'TTC'],
    sub: 'Sequential invoices with your ICE, RC and IF.\nYour whole year, already totalled.',
  },
  fines: {
    kicker: 'Traffic fines',
    title: 'A fine arrives in November\nfor something that happened in July.',
    typed: 'B-5678-C   ·   14 July, 09:42',
    reveal: 'Ahmed Benali  ·  contract #RAG-0043',
    sub: 'The plate and the exact time are all it needs.',
  },
  languages: {
    kicker: 'Languages',
    title: 'English, French and Arabic —\nwith true right-to-left.',
    tags: ['English', 'Français', 'العربية'],
  },
  whitelabel: {
    kicker: 'White-label',
    title: 'Your name.\nYour domain.\nYour database.',
    sub: 'Nothing your customer sees says DriveDesk.',
  },
  outro: {
    title: 'Run your entire\nrental agency\nfrom one desk.',
    cta: 'drivedesk.ma',
    foot: 'Book a demo · Built by Bangicode',
  },
};

const fr = {
  dir: 'ltr',
  intro: {
    kicker: 'LE SYSTÈME D’EXPLOITATION DE LA LOCATION',
    title: ['Pilotez toute', 'votre agence', 'depuis un seul poste.'],
    accentLine: 2,
  },
  problem: {
    kicker: 'Aujourd’hui',
    lines: ['Un tableau blanc.', 'Un fichier Excel.', 'Un groupe WhatsApp.', 'Un contrat sous Word.'],
    punch: ['Ça tient —', 'jusqu’au jour où non.'],
  },
  dashboard: {
    kicker: 'Chaque matin',
    title: 'Toute l’activité,\nen cinq secondes.',
    callouts: ['Véhicules sortis', 'Retours attendus', 'Entretien à prévoir', 'Revenu du jour'],
  },
  planning: {
    kicker: 'Planification',
    title: 'Remplacez le tableau blanc.',
    sub: 'Une ligne par véhicule. Une barre par location.\nChaque trou est une voiture que vous pourriez louer.',
  },
  fleet: {
    kicker: 'Flotte & clients',
    title: 'Chaque voiture a son dossier.\nChaque client aussi.',
    left: 'Immatriculation, papiers,\ntarif, historique',
    right: 'Permis, scans des pièces,\net une liste noire',
  },
  contract: {
    kicker: 'Contrats',
    title: 'Générés, à vos couleurs,\nsignés à l’écran.',
    bullets: ['Généré depuis la réservation', 'Vos identifiants légaux', 'Vos conditions', 'Signé au doigt'],
  },
  money: {
    kicker: 'Encaissement',
    title: 'Tarifé, taxé, facturé —\nautomatiquement.',
    chips: ['HT', 'TVA 20 %', 'TTC'],
    sub: 'Des factures numérotées avec vos ICE, RC et IF.\nVotre année entière, déjà totalisée.',
  },
  fines: {
    kicker: 'Contraventions',
    title: 'Une contravention arrive en novembre\npour un fait de juillet.',
    typed: 'B-5678-C   ·   14 juillet, 09:42',
    reveal: 'Ahmed Benali  ·  contrat #RAG-0043',
    sub: 'La plaque et l’heure exacte suffisent.',
  },
  languages: {
    kicker: 'Langues',
    title: 'Français, arabe et anglais —\navec un vrai droite-à-gauche.',
    tags: ['English', 'Français', 'العربية'],
  },
  whitelabel: {
    kicker: 'Marque blanche',
    title: 'Votre nom.\nVotre domaine.\nVotre base de données.',
    sub: 'Rien de ce que voit votre client ne dit DriveDesk.',
  },
  outro: {
    title: 'Pilotez toute\nvotre agence\ndepuis un seul poste.',
    cta: 'drivedesk.ma',
    foot: 'Demandez une démo · Développé par Bangicode',
  },
};

const ar = {
  dir: 'rtl',
  intro: {
    kicker: 'نظام تشغيل تأجير السيارات',
    title: ['أدر وكالتك', 'لتأجير السيارات بالكامل', 'من مكان واحد.'],
    accentLine: 2,
  },
  problem: {
    kicker: 'اليوم',
    lines: ['لوح أبيض.', 'ملف Excel.', 'مجموعة على واتساب.', 'عقد على Word.'],
    punch: ['ينجح ذلك —', 'إلى أن يتوقف.'],
  },
  dashboard: {
    kicker: 'كل صباح',
    title: 'النشاط كله،\nفي خمس ثوانٍ.',
    callouts: ['السيارات الخارجة', 'الإرجاعات المستحقة', 'الصيانة المستحقة', 'إيرادات اليوم'],
  },
  planning: {
    kicker: 'التخطيط',
    title: 'استغنِ عن اللوح الأبيض.',
    sub: 'سطر لكل سيارة. وشريط لكل عملية تأجير.\nكل فراغ هو سيارة كان بإمكانك تأجيرها.',
  },
  fleet: {
    kicker: 'الأسطول والعملاء',
    title: 'لكل سيارة ملف.\nولكل عميل ملف.',
    left: 'اللوحة، والوثائق،\nوالسعر، وسجل الصيانة',
    right: 'الرخصة، ونسخ الهوية،\nوقائمة سوداء',
  },
  contract: {
    kicker: 'العقود',
    title: 'تُنشأ تلقائيًا، بهويتك،\nوتُوقَّع على الشاشة.',
    bullets: ['يُنشأ من الحجز', 'معرّفاتك القانونية', 'شروطك الخاصة', 'يُوقَّع بالإصبع'],
  },
  money: {
    kicker: 'التحصيل',
    title: 'يُسعَّر ويُحتسب ويُفوتر —\nتلقائيًا.',
    chips: ['دون ضريبة', 'ضريبة 20%', 'الإجمالي'],
    sub: 'فواتير مرقَّمة تسلسليًا بأرقام ICE وRC وIF.\nسنتك كاملة، مجموعة سلفًا.',
  },
  fines: {
    kicker: 'المخالفات المرورية',
    title: 'تصل مخالفة في نونبر\nعن واقعة حدثت في يوليوز.',
    // Kept free of Arabic words on purpose: an Arabic month name inside this
    // Latin/numeric run gets reordered by the bidi algorithm and reads wrong.
    typed: 'B-5678-C   ·   14/07   ·   09:42',
    // Same reason the '#' is dropped — it lands on the wrong side of the ref.
    reveal: 'أحمد بنعلي  ·  العقد RAG-0043',
    sub: 'رقم اللوحة والوقت بالضبط يكفيان.',
  },
  languages: {
    kicker: 'اللغات',
    title: 'العربية والفرنسية والإنجليزية —\nمع اتجاه حقيقي من اليمين إلى اليسار.',
    tags: ['English', 'Français', 'العربية'],
  },
  whitelabel: {
    kicker: 'العلامة البيضاء',
    title: 'اسمك.\nنطاقك.\nقاعدة بياناتك.',
    sub: 'لا شيء مما يراه عميلك يذكر اسم DriveDesk.',
  },
  outro: {
    title: 'أدر وكالتك\nلتأجير السيارات بالكامل\nمن مكان واحد.',
    cta: 'drivedesk.ma',
    foot: 'اطلب عرضًا توضيحيًا · تطوير Bangicode',
  },
};

export const COPY = { en, fr, ar };

// The dashboard screenshot changes per language so the UI in the shot matches
// the words on top of it.
export const DASHBOARD_SHOT = {
  en: 'shots/en-10-dashboard.png',
  fr: 'shots/fr-10-dashboard.png',
  ar: 'shots/ar-10-dashboard-rtl.png',
};
