<?php
/**
 * English content for the new site: pages, and SEO/B2B improvements for products.
 * Facts come from ariafoods.co; wording is rewritten for wholesale buyers and for
 * search terms where Search Console showed impressions with weak positions
 * (date syrup, date powder, date paste, chopped dates, Iranian dates export).
 *
 * @package AriaModern
 */

defined( 'ABSPATH' ) || exit;

function aria_seed_pages() {
	return array(
		'home' => array(
			'title' => 'Home',
			'seo_title' => 'Iranian Dates & Date Syrup Wholesale Supplier | Aria Foods',
			'seo_desc'  => 'Wholesale supplier of Iranian dates and date ingredients: Mazafati, Piarom, Zahedi, Sayer, bulk date syrup (Brix 72–78), paste and powder. FCL/LCL export and private label.',
			'content'   => '',
		),
		'news' => array(
			'title'     => 'Insights',
			'excerpt'   => 'Harvest reports, prices and sourcing guides for importers and food manufacturers.',
			'seo_title' => 'Date Market Insights, Harvest Reports & Sourcing Guides | Aria Foods',
			'seo_desc'  => 'Market updates, harvest reports and sourcing guides for importers and food manufacturers buying Iranian dates, date syrup, paste and powder.',
			'content'   => '',
		),
		'about-us' => array(
			'title'     => 'About Aria Foods',
			'excerpt'   => 'An Iranian exporter of dates and date ingredients, backed by family-owned factories with more than 30 years in the trade.',
			'seo_title' => 'About Aria Foods – Iranian Dates Exporter Since 2016',
			'seo_desc'  => 'Aria International Trade Development Co. exports Iranian dates and date ingredients to five continents from three production and processing factories. ISO 9001, ISO 22000, HACCP.',
			'form'      => 'contact',
			'content'   => <<<'HTML'
<p class="lead">Aria International Trade Development Co. was founded in 2016 and trades under the Aria Foods brand. The company is backed by family-owned industrial units with more than 30 years of experience in dates, and exports Iranian dates and date ingredients to buyers on five continents.</p>
<h2>Three factories, one export desk</h2>
<p>Production and processing run across three factories. Raw dates come from a network of farmers we have worked with for years. On arrival they are disinfected to control agricultural pests, then washed, graded and packed on modern equipment by trained staff.</p>
<p>A dedicated export team handles transport, customs clearance and documentation, so importers deal with one contact from sample to arrival.</p>
<h2>Who we work with</h2>
<ul class="ticks">
<li><strong>Food service and bulk buyers</strong> – importers, wholesalers and fruit markets buying whole dates by the container.</li>
<li><strong>Manufacturers</strong> – confectionery, bakery, syrup and beverage factories using dates as a natural sweetener.</li>
<li><strong>Private label and premium retail</strong> – brands that sell our dates in their own packaging.</li>
</ul>
<h2>Vision and mission</h2>
<p><strong>Vision:</strong> to design, produce and supply healthy, nutritious dates and date-based ingredients.</p>
<p><strong>Mission:</strong> to raise production quality and supply-chain reliability with the support of our group companies, grow the international market for Iranian dates, find long-term trading partners worldwide and earn their trust through how we sell and serve.</p>
<h2>Certificates</h2>
<p>Our quality and food-safety systems are certified to ISO 9001, ISO 22000 and HACCP. You can download each certificate from the links on this page.</p>
HTML,
		),
		'managing-board' => array(
			'title'     => 'Managing board',
			'excerpt'   => 'Aria was formed in 2016 from three family companies; the board is the next generation of those families.',
			'seo_title' => 'Managing Board | Aria Foods',
			'seo_desc'  => 'Meet the managing board of Aria Foods, the Iranian dates exporter formed in 2016 from three family companies with nearly 30 years of combined experience.',
			'content'   => <<<'HTML'
<p class="lead">Aria Gostar Raeen Iranian was established in 2016 as a merger of three older family companies with nearly 30 years of combined experience. Its board is made up of the new generation of the founding families.</p>
<div class="people">
<section class="person">
<img src="https://ariafoods.co/wp-content/uploads/2020/01/photo_2020-01-18_13-27-13.jpg" alt="Mohammad Saeid Chitsaz Esfahani" width="480" height="640" loading="lazy">
<h2>Mohammad Saeid Chitsaz Esfahani</h2>
<p class="role">Chief Executive Officer</p>
<p>MBA and Master’s in Marketing Management, Bachelor’s in Mechanical Engineering, Isfahan University. Previously export sales manager at Caravan International Trade Co., supply manager at AriaTDC and CEO of AriaTDC’s branch in Muscat, Oman. Founder of AriaTDC.</p>
<blockquote>Knowing the product and the supply chain matters, but knowing the customer comes first.</blockquote>
</section>
<section class="person">
<img src="https://ariafoods.co/wp-content/uploads/2020/01/photo_2020-01-18_13-28-50-853x1280.jpg" alt="Mahdy Golabdar" width="480" height="640" loading="lazy">
<h2>Mahdy Golabdar</h2>
<p class="role">Chairman</p>
<p>Bachelor’s in Mechanical Engineering, Isfahan University. CEO of Palizsep Company Ltd., founder, export manager and chairman of AriaTDC.</p>
<blockquote>We are focused on improving the supply chain, strengthening our overseas operations, reducing risk for buyers and making purchasing simple through secure online services.</blockquote>
</section>
</div>
HTML,
		),
		'ariafoods-bulk-service' => array(
			'title'     => 'Bulk & food service',
			'excerpt'   => 'Dates and date ingredients by the ton for importers, wholesalers, repackers and food factories.',
			'seo_title' => 'Bulk Dates Supplier – Wholesale Iranian Dates by the Container | Aria Foods',
			'seo_desc'  => 'Buy Iranian dates in bulk: 5–10 kg cartons, 30 kg bags, date syrup in gallons and 1,400 kg IBC tanks. LCL from 1 ton, 20 ft and 40 ft containers. Request a quote.',
			'form'      => 'quote',
			'faq'       => "Q: What is the minimum order for bulk dates?\nA: LCL shipments start from 1 ton. A 20 ft container carries up to about 15 tons and a 40 ft container 25 tons or more, depending on product and packing.\nQ: How are bulk dates packed?\nA: Whole dates are packed in 5 to 10 kg cartons or 30 kg bags. Date syrup and liquid date sugar are packed in polyethylene gallons or 1,400 kg IBC tanks.\nQ: Which markets buy bulk Iranian dates?\nA: Price-sensitive markets such as India and Bangladesh mostly buy for fruit markets and whole-date consumption; buyers in Europe and North America mostly supply confectionery, syrup and food factories.",
			'content'   => <<<'HTML'
<p class="lead">Bulk (food service) is the channel for buyers who need dates in volume: importers, wholesalers, fruit markets, repackers and factories that use dates as a natural sweetener.</p>
<h2>What bulk buyers order from us</h2>
<p>Importers in price-sensitive markets such as India and Bangladesh mainly buy whole dates for fruit markets and direct consumption. Buyers in Western markets mostly supply confectionery factories, syrup producers and other manufacturers that replace refined sugar with dates.</p>
<h2>Bulk packing options</h2>
<ul class="ticks">
<li>Cartons of 5 to 10 kg for every date variety</li>
<li>30 kg bags for processing and repacking</li>
<li>Polyethylene gallons and 1,400 kg IBC tanks for date syrup and liquid date sugar</li>
</ul>
<h2>Order volumes</h2>
<table>
<thead><tr><th>Shipment</th><th>Typical volume</th></tr></thead>
<tbody>
<tr><td>LCL</td><td>1–5 tons or 5–10 tons</td></tr>
<tr><td>20 ft container</td><td>Up to about 15 tons</td></tr>
<tr><td>40 ft container</td><td>Up to about 25 tons, or more depending on packing</td></tr>
</tbody>
</table>
<p>Tell us the product, grade, packing and destination port and we will quote CFR or FOB with a shipping window.</p>
<figure><img src="https://ariafoods.co/wp-content/uploads/2026/10/BulkService.jpg" alt="Bulk date cartons and IBC tanks for export" loading="lazy"></figure>
HTML,
		),
		'customer-packing' => array(
			'title'     => 'Private label',
			'excerpt'   => 'You dream, we design: our dates in retail packs under your brand or ours.',
			'seo_title' => 'Private Label Dates – Custom Packing for Your Brand | Aria Foods',
			'seo_desc'  => 'Private label Iranian dates in 250–700 g flexible bags, pressed, vacuum, plastic and carton packs, with your logo or ours. Packed in ISO 22000 and HACCP certified facilities.',
			'form'      => 'quote',
			'faq'       => "Q: What pack sizes do you offer for private label?\nA: 250, 350, 450, 500, 600 and 700 g.\nQ: Which pack types are available?\nA: Flexible bags, pressed packs, vacuum packs, plastic packs, sealed vacuum packs and cartons.\nQ: Can you design the packaging?\nA: Yes. Send your brand guidelines and we design and print the pack, or we pack under the Aria Foods brand.",
			'content'   => <<<'HTML'
<p class="lead">Private label lets you sell Aria Foods dates in small retail packs carrying your own logo – or ours. You tell us the market; we design, print and pack.</p>
<h2>Pack types</h2>
<ul class="ticks cols-2">
<li>Flexible bag</li>
<li>Pressed pack</li>
<li>Vacuum pack</li>
<li>Sealed vacuum pack</li>
<li>Plastic pack</li>
<li>Carton</li>
</ul>
<h2>Pack weights</h2>
<p class="chips"><span>250 g</span> <span>350 g</span> <span>450 g</span> <span>500 g</span> <span>600 g</span> <span>700 g</span></p>
<figure><img src="https://ariafoods.co/wp-content/uploads/2026/10/AriaFoodsAllProducts.jpg" alt="Aria Foods private label date packs" loading="lazy"></figure>
<h2>How a private label order runs</h2>
<ol class="steps">
<li><strong>Brief</strong> – variety, grade, pack type, weight and target market.</li>
<li><strong>Design</strong> – we prepare the artwork with your brand and the label text your market requires.</li>
<li><strong>Sample</strong> – you approve a packed sample before production.</li>
<li><strong>Production and shipping</strong> – packed, palletised and shipped with export documents.</li>
</ol>
HTML,
		),
		'get-a-quote' => array(
			'title'     => 'Request a quote',
			'excerpt'   => 'Tell us the product, volume and destination port. We reply within one working day.',
			'seo_title' => 'Request a Quote for Bulk Dates & Date Syrup | Aria Foods',
			'seo_desc'  => 'Get a price for Iranian dates, date syrup, paste or powder: choose product, volume (LCL, 20 ft or 40 ft) and destination port. Reply within one working day.',
			'form'      => 'quote',
			'content'   => '',
		),
		'sample-request' => array(
			'title'     => 'Request a sample',
			'excerpt'   => 'Check the quality before you order. Tell us the products and where to send them.',
			'seo_title' => 'Request a Sample of Iranian Dates or Date Syrup | Aria Foods',
			'seo_desc'  => 'Request samples of Iranian date varieties, date syrup, paste or powder before placing a wholesale order.',
			'form'      => 'sample',
			'content'   => '',
		),
		'tracking-orders' => array(
			'title'     => 'Track an order',
			'excerpt'   => 'For confirmed orders: send your order number and we reply with the shipment status.',
			'seo_title' => 'Track Your Order | Aria Foods',
			'seo_desc'  => 'Customers with a confirmed order can request the shipment status from the Aria Foods export team.',
			'form'      => 'tracking',
			'noindex'   => 1,
			'content'   => '<p>If you have an order but have not received your customer panel details, message us on WhatsApp and the export team will send them.</p>',
		),
		'my-account' => array(
			'title'     => 'My account',
			'excerpt'   => '',
			'seo_title' => 'Customer Account | Aria Foods',
			'seo_desc'  => 'Sign in or create a business account to follow your quote and sample requests with Aria Foods.',
			'noindex'   => 1,
			'content'   => '',
		),
		'contact-us' => array(
			'title'     => 'Contact',
			'excerpt'   => 'The trade desk answers on WhatsApp, email and phone in English.',
			'seo_title' => 'Contact Aria Foods – Iranian Dates Export Desk',
			'seo_desc'  => 'Contact the Aria Foods export desk on WhatsApp, phone or email for quotes on Iranian dates, date syrup, paste and powder.',
			'form'      => 'contact',
			'content'   => '',
		),
		'ariafoods-dates-catalogue' => array(
			'title'     => 'Catalogue 2025',
			'excerpt'   => 'Every variety and ingredient with specifications and pack formats, in one PDF.',
			'seo_title' => 'Aria Foods Dates Catalogue 2025 (PDF)',
			'seo_desc'  => 'Download the Aria Foods 2025 catalogue of Iranian dates and date ingredients with specifications and packing options.',
			'form'      => 'quote',
			'content'   => '<p class="lead">The 2025 catalogue lists every date variety and ingredient we export, with specifications and packing options.</p><p><a class="btn btn--primary" href="https://ariafoods.co/wp-content/uploads/2025/07/Aria-Foods-Ctlg-2025.pdf" target="_blank" rel="noopener">Download the catalogue (PDF)</a></p>',
		),
	);
}

/**
 * Product improvements keyed by slug: SEO, packing, applications, FAQ, extra content.
 */
function aria_seed_products() {
	$dates_pack = "Bulk cartons, 5 kg and 10 kg\n30 kg bags for processing\nPrivate label retail packs, 250 g to 5 kg";
	$dates_uses = "Importers and wholesalers\nFruit markets and retail chains\nRepackers and private label brands\nHoReCa and catering";
	return array(
		'date-syrup' => array(
			'order'     => 1,
			'seo_title' => 'Bulk Date Syrup Supplier & Exporter (Brix 72–78) | Aria Foods',
			'seo_desc'  => 'Wholesale date syrup from Iran at Brix 72–78, no additives except citric acid. Packed in gallons and 1,400 kg IBC tanks for food and beverage manufacturers. Request specs.',
			'excerpt'   => 'Natural date syrup at Brix 72–78 for bakeries, beverage and dairy plants, confectionery and sauce makers. Supplied in gallons and 1,400 kg IBC tanks.',
			'packing'   => "Polyethylene gallons\n1,400 kg IBC tanks\nJars and cans for private label",
			'uses'      => "Bakery, biscuits and cereal bars\nBeverages, juices and sports drinks\nDairy, yoghurt and ice cream\nConfectionery and chocolate fillings\nSauces, dressings and marinades\nRepackers and ingredient distributors",
			'specs'     => array(
				array( 'Product', 'Date syrup (dibs)' ),
				array( 'Production method', 'Industrial extraction or traditional pressing' ),
				array( 'Brix', '72–78 (to order)' ),
				array( 'pH', '3.4–3.5' ),
				array( 'Colour', 'Dark brown' ),
				array( 'Additives', 'None except citric acid' ),
				array( 'Taste', 'Not burnt, fermented or sour' ),
				array( 'Stability', 'No granulation for at least 48 hours' ),
				array( 'Storage', 'Refrigerated at about 4 °C for long-term storage' ),
				array( 'Origin', 'Iran' ),
			),
			'faq'       => "Q: What Brix is your date syrup?\nA: We supply date syrup between Brix 72 and 78, adjusted to each order.\nQ: Does your date syrup contain additives?\nA: No. The only permitted addition is citric acid.\nQ: How is bulk date syrup packed for export?\nA: In polyethylene gallons or 1,400 kg IBC tanks, and in jars or cans for private label orders.\nQ: How should date syrup be stored?\nA: For long-term storage keep it refrigerated at around 4 °C to slow sugar crystallisation.\nQ: What is the minimum order for date syrup?\nA: LCL shipments start from 1 ton; full 20 ft and 40 ft containers are available. Tell us your monthly volume and we will plan regular shipments.\nQ: Is date syrup a replacement for sugar in food production?\nA: Yes. Manufacturers use date syrup to replace part or all of refined sugar, glucose syrup or honey in bakery, beverage, dairy and confectionery recipes.",
			'append'    => <<<'HTML'
<h2>Date syrup for food and beverage manufacturers</h2>
<p>Date syrup (also called dibs or date molasses) is the concentrated sugar of the date fruit. Manufacturers use it to replace refined sugar, glucose syrup or honey while adding colour, body and a caramel note. We supply it in bulk to factories, ingredient distributors and repackers.</p>
<h2>Specification at a glance</h2>
<ul class="ticks">
<li><strong>Brix 72–78</strong>, set to your recipe</li>
<li><strong>pH 3.4–3.5</strong>, dark brown colour</li>
<li><strong>Clean label:</strong> no additives except citric acid</li>
<li><strong>Quality:</strong> no burnt, fermented or sour taste; no granulation for at least 48 hours</li>
</ul>
<h2>Bulk packing and volumes</h2>
<p>Date syrup ships in polyethylene gallons and 1,400 kg IBC tanks. Orders start from LCL volumes of 1 ton; a 20 ft container carries up to about 15 tons. For regular buyers we plan monthly or quarterly shipments against a fixed specification.</p>
<h2>Where date syrup is used</h2>
<ul class="ticks cols-2">
<li>Bread, biscuits, cakes and cereal bars</li>
<li>Juices, smoothies and sports drinks</li>
<li>Yoghurt, flavoured milk and ice cream</li>
<li>Chocolate fillings and confectionery</li>
<li>Sauces, dressings and marinades</li>
<li>Retail jars under your brand</li>
</ul>
<h2>Storage and shipping</h2>
<p>Keep date syrup refrigerated at around 4 °C for long-term storage to slow crystallisation. Our export team arranges transport, customs and documents to your destination port.</p>
HTML,
		),
		'date-paste' => array(
			'order'     => 2,
			'seo_title' => 'Date Paste Supplier – Bulk Pitted Date Paste for Food Industry | Aria Foods',
			'seo_desc'  => 'Bulk pitted date paste from Iran, pasteurised with no preservatives, 20–26% moisture. Black and brown paste for bakery, confectionery and dairy. Request a quote.',
			'excerpt'   => 'Pasteurised, preservative-free date paste with 20–26% moisture, in black and brown types for bakery, confectionery and dairy manufacturers.',
			'packing'   => "Bulk cartons with food-grade liners\nPrivate label packs on request",
			'uses'      => "Biscuits, cookies and filled bars\nCakes and chocolate production\nDairy and ice cream\nConfectionery fillings\nEnergy balls and snack bars",
			'faq'       => "Q: What is the moisture of your date paste?\nA: 20 to 26%.\nQ: Does the paste contain preservatives?\nA: No. It is fully pasteurised and contains no preservatives.\nQ: What is the difference between black and brown date paste?\nA: Black paste is made from black dates; brown paste from yellow and brown dates.\nQ: How is date paste shipped and stored?\nA: In a refrigerated container, and kept refrigerated for up to 6 months.",
			'append'    => <<<'HTML'
<h2>Bulk date paste for manufacturers</h2>
<p>Our date paste is made by pitting, peeling and grinding dates, then controlling moisture to 20–26%. It is fully pasteurised and preservative-free, with a soft, even texture and the natural colour of the fruit.</p>
<ul class="ticks">
<li><strong>Black paste</strong> from black dates, <strong>brown paste</strong> from yellow and brown dates</li>
<li>Optional sesame, hazelnut, pistachio, almond or walnut</li>
<li>Ships in refrigerated containers; shelf life up to 6 months refrigerated</li>
</ul>
HTML,
		),
		'date-powder' => array(
			'order'     => 3,
			'seo_title' => 'Date Powder Supplier – Bulk & Wholesale Date Sugar | Aria Foods',
			'seo_desc'  => 'Wholesale date powder (date sugar) from dried Iranian dates, coarse or fine, no additives, long shelf life without refrigeration. Bulk export to food manufacturers.',
			'excerpt'   => 'Dried date powder (date sugar), coarse or fine, with no additives and a long shelf life without refrigeration. Supplied in bulk to food manufacturers.',
			'packing'   => "Bulk bags and cartons with food-grade liners\nPrivate label packs on request",
			'uses'      => "Bakery and cereal mixes\nBaby food and snacks\nSports nutrition and protein products\nSpice and seasoning blends\nPartial or full sugar replacement",
			'faq'       => "Q: What is date powder?\nA: Date powder, or date sugar, is made from dried dates ground to a coarse or fine powder. It can replace all or part of the sugar in food formulations.\nQ: Does date powder need refrigeration?\nA: No. Its low moisture lets it be stored for a long time without refrigeration.\nQ: What colour is your date powder?\nA: From light cream to brown, depending on the date variety and how much pulp is used.\nQ: Can I buy date powder in bulk?\nA: Yes. We export from LCL volumes of 1 ton up to full containers.",
		),
		'liquid-date-sugar' => array(
			'order'     => 4,
			'seo_title' => 'Liquid Date Sugar Supplier – Clear Date Syrup Brix 72–78 | Aria Foods',
			'seo_desc'  => 'Clear liquid date sugar, Brix 72–78, balanced glucose and fructose, no additives. A natural sucrose replacement for beverages, dairy and confectionery.',
			'excerpt'   => 'Clear, white to light-brown liquid date sugar at Brix 72–78 with equal glucose and fructose, for beverage, dairy and confectionery manufacturers.',
			'packing'   => "Polyethylene gallons\n1,400 kg IBC tanks",
			'uses'      => "Soft drinks, juices and sports drinks\nDairy and ice cream\nJams and marmalades\nBakery and confectionery",
			'faq'       => "Q: How is liquid date sugar different from date syrup?\nA: Liquid date sugar is clarified: it is completely clear and white to light brown, while date syrup is dark brown.\nQ: What is the Brix?\nA: 72 to 78, with sugars around 73% split evenly between glucose and fructose.\nQ: What is the shelf life?\nA: About 10 months when refrigerated.",
		),
		'chopped-dates' => array(
			'order'     => 5,
			'seo_title' => 'Chopped Dates Supplier – Diced Dates for Bakery & Cereal | Aria Foods',
			'seo_desc'  => 'Pitted, chopped Iranian dates coated in flour, sugar or cinnamon for bakery, cereal and snack manufacturers. Bulk and retail packing for export.',
			'excerpt'   => 'Pitted Iranian dates cut into small pieces and coated for free flow – an ingredient for bakery, breakfast cereal and snack producers.',
			'packing'   => "Bulk cartons with food-grade liners\nRetail packs for export",
			'uses'      => "Breakfast cereals and muesli\nBakery and biscuits\nSnack and granola bars\nIce cream inclusions",
			'faq'       => "Q: How are your chopped dates coated?\nA: With flour, sugar or cinnamon so the pieces stay free-flowing in production.\nQ: How should chopped dates be stored?\nA: At room temperature in closed containers, away from heat.",
		),
		'mazafati-dates' => array(
			'order'     => 10,
			'seo_title' => 'Mazafati Dates Wholesale Supplier (Bam / Kimia Dates) | Aria Foods',
			'seo_desc'  => 'Mazafati (Bam, Kimia) dates from Kerman in bulk and private label: soft black dates, 65–85 pieces per kg, cold-stored. Cartons, retail packs, LCL and containers.',
			'packing'   => "Retail packs, 250 g to 5 kg\nMother cartons of 12 packs (about 6.8–8.5 kg)\nPrivate label packs",
			'uses'      => $dates_uses,
			'faq'       => "Q: Are Mazafati and Kimia dates the same?\nA: Yes. Kimia is a well-known brand name for Mazafati dates, which traders also call Bam dates after the main growing area.\nQ: Do Mazafati dates need cold storage?\nA: Yes. Keep them between -5 and 5 °C; refrigerated, shelf life reaches up to two years.\nQ: When is the Mazafati harvest?\nA: From late August to early October.",
		),
		'piarom-dates' => array(
			'order'     => 11,
			'seo_title' => 'Piarom (Maryam) Dates Wholesale Supplier & Price | Aria Foods',
			'seo_desc'  => 'Premium Piarom (Maryam, chocolate) dates from Hormozgan in bulk and private label. Semi-dry, below 15% moisture, 18–24 months shelf life, no refrigeration needed.',
			'packing'   => $dates_pack,
			'uses'      => $dates_uses,
			'faq'       => "Q: What affects the price of Piarom dates?\nA: Size grade (small, normal, jumbo), harvest year, packing and volume. Send your specification and quantity for a current price.\nQ: Do Piarom dates need refrigeration?\nA: No, which makes them practical for long-distance export.",
		),
		'zahedi-dates' => array(
			'order'     => 12,
			'seo_title' => 'Zahedi Dates Bulk Supplier from Iran | Aria Foods',
			'seo_desc'  => 'Golden Zahedi dates in bulk for importers and processors: semi-dry, firm, 18–24 months shelf life without refrigeration. Exported to the UAE, Asia and Europe.',
			'packing'   => $dates_pack,
			'uses'      => $dates_uses . "\nDate paste and syrup producers",
		),
		'sayer-dates' => array(
			'order'     => 13,
			'seo_title' => 'Sayer (Stamaran) Dates Wholesale Supplier | Aria Foods',
			'seo_desc'  => 'Sayer (Stamaran) dates from Khuzestan for importers and processors: semi-dry, pit separates easily, no refrigeration needed. Bulk cartons, bags and private label.',
			'packing'   => $dates_pack,
			'uses'      => $dates_uses . "\nDate syrup, paste and powder producers",
		),
		'rabbee-dates' => array(
			'order'     => 14,
			'seo_title' => 'Rabbi Dates Bulk Supplier from Iran | Aria Foods',
			'seo_desc'  => 'Rabbi (Rabbee) dates from Sistan and Baluchestan: soft black dates, 12–18 months at room temperature, 5 and 10 kg bulk packs and 500–600 g private label.',
			'packing'   => "Bulk packs, 5 kg and 10 kg\nPrivate label packs, 500 g and 600 g",
			'uses'      => $dates_uses,
		),
		'kabkab-dates' => array(
			'order'     => 15,
			'seo_title' => 'Kabkab Dates Wholesale Supplier | Aria Foods',
			'seo_desc'  => 'Kabkab (Dashtestan) dates from Bushehr in bulk: soft, juicy, honey-sweet and competitively priced. Pressed and export packaging, 250 g to 5 kg.',
			'packing'   => $dates_pack . "\nTraditional pressed packs",
			'uses'      => $dates_uses,
		),
		'lulu-dates' => array(
			'order'     => 16,
			'seo_title' => 'Lulu Dates Wholesale Supplier | Aria Foods',
			'seo_desc'  => 'Lulu (plum) dates, the smallest Iranian date, in bulk and private label for snack and retail buyers. No refrigerated transport needed.',
			'packing'   => $dates_pack,
			'uses'      => $dates_uses,
		),
		'kalute-dates' => array(
			'order'     => 17,
			'seo_title' => 'Kalute Dates Wholesale Supplier | Aria Foods',
			'seo_desc'  => 'Kalute (Kali) dates from southern Iran in bulk: soft, juicy and moderately priced, similar to Mazafati. Cartons and private label packs.',
			'packing'   => $dates_pack,
			'uses'      => $dates_uses,
		),
		'khassui-dates' => array(
			'order'     => 18,
			'seo_title' => 'Khassui Dates Wholesale Supplier | Aria Foods',
			'seo_desc'  => 'Khassui (Khasoei) dates from Kerman, semi-dry or soft, in bulk and private label. Long shelf life and a very sweet flavour.',
			'packing'   => $dates_pack,
			'uses'      => $dates_uses,
		),
	);
}
