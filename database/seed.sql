-- ============================================================
--  LANDPLAN.CO.KE, starter/seed data
--  Import AFTER schema.sql. Mirrors the current static website so
--  the site looks identical once it is database-driven.
--
--  NOTE: the first admin account is NOT seeded here (a password hash
--  must be generated on the server). Create it once by visiting
--  /admin/setup.php after upload, then delete that file.
-- ============================================================
SET NAMES utf8mb4;

-- ---------- Settings ----------
INSERT INTO settings (setting_key, setting_value) VALUES
 ('site_name',        'Landplan.co.ke'),
 ('tagline',          'Your One Stop Shop for Land & Property Solutions'),
 ('contact_phone',    '+254 705 121 788'),
 ('contact_email',    'info@landplan.co.ke'),
 ('contact_location', 'Nairobi, Kenya'),
 ('social_facebook',  '#'),
 ('social_instagram', '#'),
 ('social_linkedin',  '#'),
 ('social_whatsapp',  'https://wa.me/254705121788'),
 ('lead_notify_email','info@landplan.co.ke'),
 ('hero_kicker',      'Trusted. Transparent. Professional.'),
 ('hero_title',       'Your One Stop Shop for Land & Property Solutions'),
 ('hero_sub',         'We sell land, design dream spaces, build quality homes and develop projects that last generations.'),
 ('stat_years',       '10'),
 ('stat_clients',     '5000'),
 ('stat_acres',       '1000'),
 ('stat_projects',    '300'),
 ('meta_description', 'We sell land, design dream spaces, build quality homes and develop projects that last generations. Trusted. Transparent. Professional.');

-- ---------- Land listings ----------
INSERT INTO land_listings (title, slug, location, size, price, category, title_status, description, features, cover_image, featured, status) VALUES
 ('1/8 Acre Residential Plot', 'kitengela-1-8-acre', 'Kitengela, Kajiado', '1/8 Acre Plot', 1250000, 'Residential', 'Ready Title Deed',
  'Prime 1/8 acre residential plot in fast-growing Kitengela. Ideal for immediate development or investment. Serviced with electricity, water and an all-weather access road.',
  "Ready title deed\nElectricity on site\nWater access\nAll-weather road\nGated community", 'assets/img/land-kitengela.jpg', 1, 'published'),
 ('1/4 Acre Commercial Plot', 'ruiru-1-4-acre', 'Ruiru, Kiambu', '1/4 Acre Plot', 2850000, 'Commercial', 'Ready Title Deed',
  'Strategically located 1/4 acre commercial plot in Ruiru, along a busy access road with high visibility. Perfect for retail, offices or mixed-use development.',
  "Ready title deed\nTarmac frontage\nHigh foot traffic\nPower & water\nClose to town", 'assets/img/land-ruiru.jpg', 1, 'published'),
 ('1/8 Acre Residential Plot', 'joska-1-8-acre', 'Joska, Kamulu', '1/8 Acre Plot', 650000, 'Residential', 'Ready Title Deed',
  'Affordable 1/8 acre residential plot in Joska, Kamulu. A great entry-level investment in a rapidly appreciating neighbourhood.',
  "Ready title deed\nAffordable pricing\nGrowing neighbourhood\nMurram road access", 'assets/img/land-joska.jpg', 1, 'published'),
 ('1/2 Acre Mixed-Use Plot', 'thika-1-2-acre', 'Thika, Kiambu', '1/2 Acre Plot', 3950000, 'Mixed Use', 'Ready Title Deed',
  'Spacious 1/2 acre mixed-use plot in Thika, suitable for residential, commercial or institutional development. Close to superhighway and amenities.',
  "Ready title deed\nNear superhighway\nFlat, well-drained\nPower & water\nHigh appreciation", 'assets/img/land-thika.jpg', 1, 'published');

-- ---------- Houses ----------
INSERT INTO houses (title, slug, location, bedrooms, bathrooms, price, description, features, cover_image, featured, status) VALUES
 ('Modern 4-Bedroom Villa', 'runda-4bed-villa', 'Runda, Nairobi', 4, 4, 42000000, 'Elegant modern villa in the leafy suburb of Runda. Open-plan living, chef''s kitchen, landscaped garden and DSQ.', "Master ensuite\nDSQ\nLandscaped garden\nSolar water heating", 'assets/img/proj-villa.jpg', 1, 'published'),
 ('3-Bedroom Bungalow', 'kitengela-3bed-bungalow', 'Kitengela, Kajiado', 3, 2, 8900000, 'Well-finished 3-bedroom bungalow on an eighth of an acre in Kitengela. Move-in ready.', "All ensuite\nParking\nReady title", 'assets/img/svc-houses.jpg', 0, 'published'),
 ('2-Bedroom Apartment', 'ruiru-2bed-apartment', 'Ruiru, Kiambu', 2, 2, 5200000, 'Contemporary 2-bedroom apartment in a secure gated development in Ruiru with borehole water and backup power.', "Borehole water\nBackup power\nLift\nCCTV", 'assets/img/proj-apartments.jpg', 0, 'published'),
 ('5-Bedroom Maisonette', 'kiambu-5bed-maisonette', 'Kiambu Town, Kiambu', 5, 4, 22500000, 'Spacious family maisonette with a large compound in Kiambu Town.', "Family room\nStudy\nDSQ\nDouble garage", 'assets/img/proj-villa.jpg', 0, 'published'),
 ('3-Bedroom Townhouse', 'thika-3bed-townhouse', 'Thika, Kiambu', 3, 3, 9800000, 'Modern 3-bedroom townhouse in a gated court in Thika.', "Gated court\nAll ensuite\nPlayground", 'assets/img/svc-houses.jpg', 0, 'published'),
 ('4-Bedroom Townhouse', 'ngong-4bed-townhouse', 'Ngong, Kajiado', 4, 3, 13400000, 'Stylish 4-bedroom townhouse with hills views in Ngong.', "Hills view\nDSQ\nSolar heating", 'assets/img/proj-apartments.jpg', 0, 'published'),
 ('2-Bedroom Bungalow', 'athi-river-2bed-bungalow', 'Athi River, Machakos', 2, 1, 6300000, 'Cosy 2-bedroom starter home in Athi River.', "Ready title\nParking\nFenced", 'assets/img/svc-houses.jpg', 0, 'published'),
 ('5-Bedroom Luxury Villa', 'karen-5bed-villa', 'Karen, Nairobi', 5, 5, 65000000, 'Luxury villa on half an acre in Karen with a pool, home office and staff quarters.', "Swimming pool\nHome office\nStaff quarters\nMature garden", 'assets/img/proj-villa.jpg', 1, 'published');

-- ---------- Projects ----------
INSERT INTO projects (title, slug, location, description, cover_image, status, featured) VALUES
 ('Greenview Apartments', 'greenview-apartments', 'Ruiru, Kiambu', 'A modern residential apartment development offering affordable, quality urban living with shared amenities.', 'assets/img/proj-apartments.jpg', 'ongoing', 1),
 ('Acacia Gated Community', 'acacia-gated-community', 'Kitengela, Kajiado', 'A secure gated community of serviced plots and homes with a controlled entrance, perimeter wall and internal roads.', 'assets/img/proj-gate.jpg', 'completed', 1),
 ('Meadow Heights Estate', 'meadow-heights-estate', 'Thika, Kiambu', 'A master-planned estate with residential plots, green spaces and full infrastructure.', 'assets/img/proj-aerial.jpg', 'completed', 0),
 ('Sunset Villa', 'sunset-villa', 'Karen, Nairobi', 'A bespoke luxury villa designed and built end-to-end for a private client.', 'assets/img/proj-villa.jpg', 'completed', 0);

-- ---------- Services ----------
INSERT INTO services (title, slug, icon, excerpt, body, cover_image, sort, status) VALUES
 ('Land for Sale', 'land-for-sale', 'land', 'Residential, commercial, agricultural & mixed-use plots in prime locations.', 'We offer verified plots with genuine, ready title deeds across Kenya''s fastest-growing towns.', 'assets/img/svc-land.jpg', 1, 'published'),
 ('Architecture & Design', 'architecture-design', 'arch', 'Modern, functional and sustainable designs tailored to your lifestyle.', 'Our architects deliver modern, functional and sustainable designs tailored to your lifestyle and budget.', 'assets/img/svc-arch.jpg', 2, 'published'),
 ('Building & Construction', 'building-construction', 'construction', 'Quality construction, on time and within budget. We build your vision.', 'From foundation to finishing, we deliver quality construction on time and within budget.', 'assets/img/svc-construction.jpg', 3, 'published'),
 ('Project Development', 'project-development', 'project', 'From concept to completion, we develop impactful residential & commercial projects.', 'We develop impactful residential and commercial projects from concept to completion.', 'assets/img/svc-project.jpg', 4, 'published'),
 ('Houses for Sale', 'houses-for-sale', 'house', 'Move-in ready homes in great neighborhoods. Find your dream home.', 'Browse move-in ready homes in great neighbourhoods across the region.', 'assets/img/svc-houses.jpg', 5, 'published');

-- ---------- Articles / blog ----------
INSERT INTO articles (title, slug, excerpt, body, cover_image, category, status, published_at) VALUES
 ('The Complete Guide to Buying Land in Kenya (2026)', 'guide-to-buying-land-in-kenya', 'Everything you need to know before you buy, from due diligence to title transfer.', '<p>Buying land in Kenya is one of the surest ways to build long-term wealth, but only if you do it right. This guide walks you through search, due diligence, agreements and title transfer.</p>', 'assets/img/land-kitengela.jpg', 'Land Buying', 'published', '2026-01-15 09:00:00'),
 ('A Practical Guide to Building Your Home in Kenya', 'guide-to-building-your-home', 'Costs, approvals, timelines and how to avoid the most common building mistakes.', '<p>From approvals to finishing, building a home in Kenya has many moving parts. Here is a practical, step-by-step overview.</p>', 'assets/img/svc-construction.jpg', 'Building', 'published', '2026-02-02 09:00:00'),
 ('Kenya Real Estate Market Trends 2026', 'kenya-real-estate-trends-2026', 'Where prices are heading and which corridors offer the best value this year.', '<p>We break down the data on where land and housing demand is growing fastest in 2026.</p>', 'assets/img/proj-aerial.jpg', 'Market Trends', 'published', '2026-03-10 09:00:00'),
 ('Why Thika Remains a Smart Bet for Land Investors', 'why-thika-is-a-smart-bet', 'Infrastructure, industry and demand keep pushing Thika land values up.', '<p>Thika''s superhighway, industry and growing population make it a standout for land investors.</p>', 'assets/img/land-thika.jpg', 'Investment', 'published', '2026-03-22 09:00:00'),
 ('5 Legal Checks to Run Before You Buy Any Property', '5-legal-checks-before-you-buy', 'A quick checklist that can save you from costly land-buying fraud.', '<p>Run these five checks, search, survey, rates, land control board and identity, before any purchase.</p>', 'assets/img/land-ruiru.jpg', 'Legal', 'published', '2026-04-05 09:00:00'),
 ('Gated Communities: Are They Worth the Premium?', 'are-gated-communities-worth-it', 'Weighing security, amenities and resale value against the price.', '<p>Gated communities cost more up front. Here is how to decide whether the premium pays off for you.</p>', 'assets/img/proj-gate.jpg', 'Lifestyle', 'published', '2026-04-20 09:00:00');

-- ---------- FAQs ----------
INSERT INTO faqs (question, answer, category, sort, status) VALUES
 ('Do your plots have ready title deeds?', 'Yes. Every plot we sell comes with a genuine, ready title deed. We also carry out full due diligence on your behalf before purchase.', 'Land', 1, 'published'),
 ('Do you offer payment plans?', 'Yes. We offer flexible instalment plans on most plots. Talk to our team for the terms on a specific plot.', 'Payments', 2, 'published'),
 ('Can you build a house for me on a plot I buy?', 'Absolutely. We are a one-stop shop, we can design and build your home end-to-end after you acquire land.', 'Construction', 3, 'published'),
 ('How do I arrange a site visit?', 'Contact us by phone, WhatsApp or the enquiry form and we will schedule a free site visit at your convenience.', 'General', 4, 'published');

-- ---------- Testimonials ----------
INSERT INTO testimonials (name, location, quote, rating, sort, status) VALUES
 ('Brian M.', 'Nairobi', 'Landplan made the whole process of buying land so easy and transparent. The best in the business!', 4, 1, 'published'),
 ('Mercy W.', 'Kiambu', 'They designed and built our dream home from scratch. Professional, timely and very reliable.', 4, 2, 'published'),
 ('James K.', 'Machakos', 'I love that they offer everything under one roof. From land to construction, they''ve got you covered.', 4, 3, 'published');

-- ---------- Editable pages ----------
INSERT INTO pages (slug, title, body, meta_title, meta_description, status) VALUES
 ('about', 'About Us', '<p>Landplan.co.ke is a leading land and property solutions company in Kenya. We offer a seamless experience from acquiring land to designing, building and developing world-class projects.</p>', 'About Us | Landplan.co.ke', 'Learn about Landplan.co.ke, a leading land and property solutions company in Kenya.', 'published'),
 ('terms', 'Terms & Conditions', '<p>Please read these terms and conditions carefully before using our website and services.</p>', 'Terms & Conditions | Landplan.co.ke', 'Terms and conditions for using Landplan.co.ke.', 'published'),
 ('privacy', 'Privacy Policy', '<p>This privacy policy explains how we collect, use and protect your personal information.</p>', 'Privacy Policy | Landplan.co.ke', 'How Landplan.co.ke collects, uses and protects your data.', 'published');
