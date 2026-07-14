-- ============================================================
--  Extra land listings (the other 8 from the original page).
--  Your database already has the first 4, so import THIS file once
--  in phpMyAdmin to top it up to 12. Safe to run once.
-- ============================================================
SET NAMES utf8mb4;

INSERT INTO land_listings (title, slug, location, size, price, category, title_status, description, features, cover_image, featured, status) VALUES
 ('1 Acre Agricultural Land', 'machakos-1-acre-agri', 'Machakos Town, Machakos', '1 Acre', 2200000, 'Agricultural', 'Ready Title Deed',
  'A full acre of fertile agricultural land in Machakos, ideal for farming or a country home. Good access and reliable rainfall in the area.',
  "Ready title deed\nFertile red soil\nAll-weather road\nSuitable for farming", 'assets/img/land-kitengela.jpg', 0, 'published'),

 ('1/4 Acre Residential Plot', 'athi-river-1-4-acre', 'Athi River, Machakos', '1/4 Acre', 1800000, 'Residential', 'Ready Title Deed',
  'A quarter-acre residential plot in Athi River, close to the EPZ and the expressway. A solid pick for a family home or a rental development.',
  "Ready title deed\nNear the expressway\nWater and power nearby\nFast-growing area", 'assets/img/land-ruiru.jpg', 0, 'published'),

 ('1/2 Acre Commercial Plot', 'kiambu-1-2-acre-commercial', 'Kiambu Town, Kiambu', '1/2 Acre', 6500000, 'Commercial', 'Ready Title Deed',
  'Half an acre with prime frontage in the heart of Kiambu Town. Well suited to retail, offices or a mixed-use building.',
  "Ready title deed\nTown-centre location\nTarmac frontage\nHigh footfall", 'assets/img/land-joska.jpg', 0, 'published'),

 ('2 Acre Agricultural Land', 'kajiado-2-acre-agri', 'Kajiado Central, Kajiado', '2 Acre', 3400000, 'Agricultural', 'Ready Title Deed',
  'Two acres of open land in Kajiado, great for farming, livestock or a weekend getaway plot. Plenty of space and clean title.',
  "Ready title deed\nOpen, flat land\nBorehole potential\nRoom to expand", 'assets/img/land-thika.jpg', 0, 'published'),

 ('1/8 Acre Residential Plot', 'ngong-1-8-acre', 'Ngong, Kajiado', '1/8 Acre', 1450000, 'Residential', 'Ready Title Deed',
  'A neat eighth-acre plot in Ngong with cool weather and lovely hills views. Ready to build in an established neighbourhood.',
  "Ready title deed\nHills views\nCool climate\nEstablished neighbourhood", 'assets/img/land-kitengela.jpg', 0, 'published'),

 ('1/4 Acre Mixed-Use Plot', 'thika-1-4-acre-mixed', 'Makongeni, Thika', '1/4 Acre', 2100000, 'Mixed Use', 'Ready Title Deed',
  'A flexible quarter-acre in Makongeni, Thika, zoned for both homes and light commercial use. Close to schools and the highway.',
  "Ready title deed\nMixed-use zoning\nNear schools\nHighway access", 'assets/img/land-ruiru.jpg', 0, 'published'),

 ('1/2 Acre Residential Plot', 'joska-1-2-acre', 'Joska, Kamulu', '1/2 Acre', 3200000, 'Residential', 'Ready Title Deed',
  'A spacious half-acre in Joska, room for a big family home and a garden. A quiet, fast-appreciating area just off Kangundo Road.',
  "Ready title deed\nSpacious plot\nQuiet neighbourhood\nOff Kangundo Road", 'assets/img/land-joska.jpg', 0, 'published'),

 ('1 Acre Commercial Plot', 'kitengela-1-acre-commercial', 'Kitengela, Kajiado', '1 Acre', 8900000, 'Commercial', 'Ready Title Deed',
  'A full commercial acre on a busy stretch of Kitengela, ideal for a showroom, godown or shopping development. Excellent visibility.',
  "Ready title deed\nBusy main road\nGreat visibility\nIdeal for development", 'assets/img/land-thika.jpg', 0, 'published');
