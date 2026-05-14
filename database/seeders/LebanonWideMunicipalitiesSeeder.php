<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Municipality;
use App\Models\Office;
use App\Models\Service;
use Illuminate\Database\Seeder;

/**
 * Adds many Lebanese municipalities (one office each) with categories and services for local performance testing.
 * Idempotent: uses firstOrCreate / updateOrCreate. Does not replace offices that already exist.
 *
 * Rough scale: ~32 municipalities × 2 categories × 3 services ≈ 192 service rows (+ municipalities, offices, categories).
 */
class LebanonWideMunicipalitiesSeeder extends Seeder
{
    public function run(): void
    {
        $workingHours = [
            'monday' => ['is_open' => true, 'open_time' => '08:00', 'close_time' => '14:00'],
            'tuesday' => ['is_open' => true, 'open_time' => '08:00', 'close_time' => '14:00'],
            'wednesday' => ['is_open' => true, 'open_time' => '08:00', 'close_time' => '14:00'],
            'thursday' => ['is_open' => true, 'open_time' => '08:00', 'close_time' => '14:00'],
            'friday' => ['is_open' => true, 'open_time' => '08:00', 'close_time' => '12:30'],
            'saturday' => ['is_open' => false, 'open_time' => null, 'close_time' => null],
            'sunday' => ['is_open' => false, 'open_time' => null, 'close_time' => null],
        ];

        $categoryNames = [
            'Civil Status & Registry',
            'Building & Urban Planning',
            'Business Licenses & Commerce',
            'Public Works & Streets',
            'Sanitation & Environment',
            'Health & Safety Inspections',
            'Community & Events',
            'Property & Zoning Certificates',
        ];

        $serviceTemplates = $this->serviceTemplates();

        foreach ($this->lebaneseMunicipalities() as $index => $row) {
            $municipalityName = 'Municipality of '.$row['name'];
            $municipality = Municipality::firstOrCreate(
                ['name' => $municipalityName],
            );

            $office = Office::query()->firstOrCreate(
                ['municipality_id' => $municipality->id],
                [
                    'name' => $row['name'].' Municipal Office',
                    'address' => $row['address'],
                    'latitude' => $row['lat'],
                    'longitude' => $row['lng'],
                    'working_hours' => $workingHours,
                    'contact_info' => $row['phone'],
                ],
            );

            $catA = $categoryNames[$index % count($categoryNames)];
            $catB = $categoryNames[($index + 3) % count($categoryNames)];

            foreach ([$catA, $catB] as $catOffset => $categoryName) {
                $category = Category::firstOrCreate(
                    [
                        'office_id' => $office->id,
                        'name' => $categoryName,
                    ],
                );

                for ($s = 0; $s < 3; $s++) {
                    $templateIndex = ($index * 6 + $catOffset * 3 + $s) % count($serviceTemplates);
                    $svc = $serviceTemplates[$templateIndex];

                    Service::updateOrCreate(
                        [
                            'office_id' => $office->id,
                            'name' => $svc['name'],
                        ],
                        [
                            'category_id' => $category->id,
                            'description' => $svc['description'],
                            'price' => $svc['price'],
                            'duration_days' => $svc['duration_days'],
                            'required_documents' => $svc['required_documents'],
                        ],
                    );
                }
            }
        }
    }

    /**
     * @return list<array{name: string, address: string, lat: float, lng: float, phone: string}>
     */
    private function lebaneseMunicipalities(): array
    {
        return [
            ['name' => 'Beirut', 'address' => 'Beirut Municipality, Weygand Street, Beirut Governorate', 'lat' => 33.8959, 'lng' => 35.5019, 'phone' => '+961 1 987 654'],
            ['name' => 'Baabda', 'address' => 'Baabda Municipal Building, Baabda Main Road, Mount Lebanon', 'lat' => 33.8337, 'lng' => 35.5444, 'phone' => '+961 5 922 410'],
            ['name' => 'Tripoli', 'address' => 'Tripoli Municipality, El-Mina Road, North Governorate', 'lat' => 34.4346, 'lng' => 35.8362, 'phone' => '+961 6 443 000'],
            ['name' => 'Sidon', 'address' => 'Saida Municipality, Riad Solh Street, South Governorate', 'lat' => 33.5631, 'lng' => 35.3689, 'phone' => '+961 7 720 100'],
            ['name' => 'Tyre', 'address' => 'Sour Municipality, Sea Road, South Governorate', 'lat' => 33.2733, 'lng' => 35.2033, 'phone' => '+961 7 740 200'],
            ['name' => 'Byblos', 'address' => 'Jbeil Municipality, Old Souk Area, Mount Lebanon', 'lat' => 34.1211, 'lng' => 35.6511, 'phone' => '+961 9 540 800'],
            ['name' => 'Batroun', 'address' => 'Batroun Municipality, Main Highway, North Governorate', 'lat' => 34.2553, 'lng' => 35.6586, 'phone' => '+961 6 730 150'],
            ['name' => 'Zgharta', 'address' => 'Zgharta Municipality, Town Center, North Governorate', 'lat' => 34.3986, 'lng' => 35.8944, 'phone' => '+961 6 660 300'],
            ['name' => 'Bcharre', 'address' => 'Bcharre Municipality, Kadisha Valley, North Governorate', 'lat' => 34.2508, 'lng' => 36.0106, 'phone' => '+961 6 671 200'],
            ['name' => 'Halba', 'address' => 'Halba Municipality, Akkar Governorate', 'lat' => 34.5436, 'lng' => 36.0797, 'phone' => '+961 6 950 100'],
            ['name' => 'Hermel', 'address' => 'Hermel Municipality, Baalbek-Hermel Governorate', 'lat' => 34.4000, 'lng' => 36.3833, 'phone' => '+961 8 200 400'],
            ['name' => 'Baalbek', 'address' => 'Baalbek Municipality, Roman Ruins District', 'lat' => 34.0058, 'lng' => 36.2181, 'phone' => '+961 8 370 500'],
            ['name' => 'Nabatieh', 'address' => 'Nabatieh Municipality, Nabatieh Governorate', 'lat' => 33.3781, 'lng' => 35.4839, 'phone' => '+961 7 760 300'],
            ['name' => 'Marjayoun', 'address' => 'Marjayoun Municipality, South Governorate', 'lat' => 33.3597, 'lng' => 35.5917, 'phone' => '+961 7 780 150'],
            ['name' => 'Bent Jbeil', 'address' => 'Bint Jbeil Municipality, Nabatieh Governorate', 'lat' => 33.1286, 'lng' => 35.4333, 'phone' => '+961 7 790 200'],
            ['name' => 'Hasbaya', 'address' => 'Hasbaya Municipality, Nabatieh Governorate', 'lat' => 33.3975, 'lng' => 35.6856, 'phone' => '+961 7 770 100'],
            ['name' => 'Jezzine', 'address' => 'Jezzine Municipality, South Governorate', 'lat' => 33.5414, 'lng' => 35.5844, 'phone' => '+961 7 810 250'],
            ['name' => 'Aley', 'address' => 'Aley Municipality, Mount Lebanon Governorate', 'lat' => 33.9167, 'lng' => 35.6000, 'phone' => '+961 5 555 100'],
            ['name' => 'Choueifat', 'address' => 'Choueifat Municipality, Mount Lebanon Governorate', 'lat' => 33.7231, 'lng' => 35.3878, 'phone' => '+961 5 430 200'],
            ['name' => 'Damour', 'address' => 'Damour Municipality, Mount Lebanon Governorate', 'lat' => 33.9181, 'lng' => 35.4517, 'phone' => '+961 5 420 300'],
            ['name' => 'Sin el Fil', 'address' => 'Sin el Fil Municipality, Mount Lebanon Governorate', 'lat' => 33.8875, 'lng' => 35.5456, 'phone' => '+961 1 485 200'],
            ['name' => 'Dekwaneh', 'address' => 'Dekwaneh Municipality, Mount Lebanon Governorate', 'lat' => 33.8800, 'lng' => 35.5500, 'phone' => '+961 1 690 400'],
            ['name' => 'Antelias', 'address' => 'Antelias Municipality, Mount Lebanon Governorate', 'lat' => 33.9089, 'lng' => 35.5903, 'phone' => '+961 4 521 100'],
            ['name' => 'Bhamdoun', 'address' => 'Bhamdoun Municipality, Aley District, Mount Lebanon', 'lat' => 33.7950, 'lng' => 35.6511, 'phone' => '+961 5 220 150'],
            ['name' => 'Baakline', 'address' => 'Baakline Municipality, Chouf District, Mount Lebanon', 'lat' => 33.6944, 'lng' => 35.5581, 'phone' => '+961 5 250 300'],
            ['name' => 'Chekka', 'address' => 'Chekka Municipality, North Governorate', 'lat' => 34.2881, 'lng' => 35.7061, 'phone' => '+961 6 790 100'],
            ['name' => 'Amioun', 'address' => 'Amioun Municipality, Koura District, North Governorate', 'lat' => 34.3000, 'lng' => 35.8081, 'phone' => '+961 6 320 200'],
            ['name' => 'Rashaya', 'address' => 'Rashaya Municipality, West Bekaa', 'lat' => 33.4000, 'lng' => 35.8500, 'phone' => '+961 8 640 100'],
            ['name' => 'Chtaura', 'address' => 'Chtaura Municipality, Zahle District, Bekaa', 'lat' => 33.8167, 'lng' => 35.8500, 'phone' => '+961 8 543 200'],
            ['name' => 'Jounieh', 'address' => 'Jounieh Municipality, Keserwan, Mount Lebanon', 'lat' => 33.9808, 'lng' => 35.6178, 'phone' => '+961 9 830 550'],
            ['name' => 'Ghazieh', 'address' => 'Ghazieh Municipality, South Governorate', 'lat' => 33.5167, 'lng' => 35.3667, 'phone' => '+961 7 730 400'],
            ['name' => 'Zahle', 'address' => 'Zahle Municipality, Boulevard, Bekaa Governorate', 'lat' => 33.8468, 'lng' => 35.9020, 'phone' => '+961 8 821 110'],
        ];
    }

    /**
     * @return list<array{name: string, description: string, price: float, duration_days: int, required_documents: list<string>}>
     */
    private function serviceTemplates(): array
    {
        return [
            [
                'name' => 'Individual Civil Status Record',
                'description' => 'Request an official individual civil status record for public administration, school, or legal use.',
                'price' => 2.00,
                'duration_days' => 3,
                'required_documents' => ['Lebanese ID or passport copy', 'Family civil registry extract if available'],
            ],
            [
                'name' => 'Family Civil Registry Extract',
                'description' => 'Request a family civil registry extract through the municipal civil registry desk.',
                'price' => 3.00,
                'duration_days' => 4,
                'required_documents' => ['Applicant ID copy', 'Family registry number or mukhtar statement'],
            ],
            [
                'name' => 'Proof of Residence Certificate',
                'description' => 'Municipal proof of residence for residents within municipal boundaries.',
                'price' => 5.00,
                'duration_days' => 2,
                'required_documents' => ['ID copy', 'Rental contract or deed', 'Utility bill or mukhtar statement'],
            ],
            [
                'name' => 'Building Permit File Submission',
                'description' => 'Preliminary building permit file for municipal technical review.',
                'price' => 75.00,
                'duration_days' => 15,
                'required_documents' => ['Property deed', 'Signed architectural plans', 'Engineer syndicate authorization'],
            ],
            [
                'name' => 'Occupancy Certificate Request',
                'description' => 'Occupancy certificate after construction completion and inspection.',
                'price' => 50.00,
                'duration_days' => 10,
                'required_documents' => ['Approved permit', 'Engineer completion report', 'Site photos'],
            ],
            [
                'name' => 'Road Occupancy Permission',
                'description' => 'Temporary use of sidewalk or road for works, scaffolding, or loading.',
                'price' => 25.00,
                'duration_days' => 5,
                'required_documents' => ['Applicant ID', 'Location map', 'Work schedule', 'Safety plan if required'],
            ],
            [
                'name' => 'Commercial Shop License',
                'description' => 'Municipal license for retail, office, workshop, or hospitality.',
                'price' => 40.00,
                'duration_days' => 12,
                'required_documents' => ['Manager ID', 'Commercial registration', 'Lease or deed', 'Shop photos'],
            ],
            [
                'name' => 'Advertisement Sign Permit',
                'description' => 'Approval for storefront sign installation or renewal.',
                'price' => 20.00,
                'duration_days' => 6,
                'required_documents' => ['Applicant ID', 'Shop license copy', 'Sign design with dimensions'],
            ],
            [
                'name' => 'Event Municipal Approval',
                'description' => 'Notice for public cultural, community, or commercial events.',
                'price' => 15.00,
                'duration_days' => 7,
                'required_documents' => ['Organizer ID', 'Event program', 'Venue agreement', 'Security plan'],
            ],
            [
                'name' => 'Road Damage Repair Request',
                'description' => 'Report potholes or sidewalk hazards for municipal works.',
                'price' => 0.00,
                'duration_days' => 7,
                'required_documents' => ['Location or map pin', 'Photos', 'Contact phone'],
            ],
            [
                'name' => 'Street Lighting Maintenance',
                'description' => 'Report broken or unsafe public street lighting.',
                'price' => 0.00,
                'duration_days' => 5,
                'required_documents' => ['Landmark description', 'Photo of pole or segment'],
            ],
            [
                'name' => 'Large Waste Pickup Request',
                'description' => 'Coordinate collection of bulky household waste.',
                'price' => 10.00,
                'duration_days' => 4,
                'required_documents' => ['Applicant ID', 'Pickup address', 'Item list or photos'],
            ],
            [
                'name' => 'Tree Trimming / Vegetation Request',
                'description' => 'Request trimming of municipal trees affecting safety or utilities.',
                'price' => 0.00,
                'duration_days' => 8,
                'required_documents' => ['Location', 'Photos', 'Neighbor consent if private line'],
            ],
            [
                'name' => 'Graffiti / Illegal Dumping Report',
                'description' => 'Report illegal dumping or graffiti on public property.',
                'price' => 0.00,
                'duration_days' => 5,
                'required_documents' => ['GPS or landmark', 'Photos', 'Optional witness contact'],
            ],
            [
                'name' => 'Food Establishment Inspection Booking',
                'description' => 'Schedule a municipal hygiene inspection for a food business.',
                'price' => 30.00,
                'duration_days' => 10,
                'required_documents' => ['Trade license copy', 'Manager ID', 'Kitchen layout sketch'],
            ],
            [
                'name' => 'Swimming Pool Safety Declaration',
                'description' => 'Submit safety compliance for residential or commercial pools.',
                'price' => 35.00,
                'duration_days' => 8,
                'required_documents' => ['Property deed or lease', 'Fence/barrier plan', 'Water treatment note'],
            ],
            [
                'name' => 'Public Hall Rental Request',
                'description' => 'Book a municipal hall for weddings, conferences, or community use.',
                'price' => 120.00,
                'duration_days' => 14,
                'required_documents' => ['Applicant ID', 'Event date and hours', 'Insurance certificate'],
            ],
            [
                'name' => 'Farmers Market Stall Permit',
                'description' => 'Short-term permit for stall at approved municipal market days.',
                'price' => 12.00,
                'duration_days' => 4,
                'required_documents' => ['Vendor ID', 'Product list', 'Health ministry card if food'],
            ],
            [
                'name' => 'Zoning Certificate (Non-Objection)',
                'description' => 'Certificate stating proposed use aligns with municipal zoning.',
                'price' => 18.00,
                'duration_days' => 9,
                'required_documents' => ['Plot map', 'Owner ID', 'Brief project description'],
            ],
            [
                'name' => 'Heritage Building Alteration Review',
                'description' => 'Municipal review for alterations in heritage or protected façades.',
                'price' => 45.00,
                'duration_days' => 20,
                'required_documents' => ['Photos of façade', 'Architect drawings', 'Heritage committee form'],
            ],
            [
                'name' => 'Excavation / Shoring Permit',
                'description' => 'Permit for excavation affecting public road or sidewalk.',
                'price' => 55.00,
                'duration_days' => 12,
                'required_documents' => ['Engineer method statement', 'Insurance', 'Traffic diversion plan'],
            ],
            [
                'name' => 'Generator Noise Complaint Intake',
                'description' => 'Formal intake for excessive generator noise; municipal follow-up.',
                'price' => 0.00,
                'duration_days' => 6,
                'required_documents' => ['Address', 'Time logs or recordings if any', 'Contact'],
            ],
            [
                'name' => 'Street Naming / Numbering Request',
                'description' => 'Request official address numbering for new subdivisions.',
                'price' => 22.00,
                'duration_days' => 18,
                'required_documents' => ['Survey map', 'Owner ID', 'Engineer subdivision plan'],
            ],
            [
                'name' => 'Public Garden / Park Maintenance Ticket',
                'description' => 'Report damaged benches, irrigation, or unsafe playground equipment.',
                'price' => 0.00,
                'duration_days' => 5,
                'required_documents' => ['Park name', 'Photo', 'Approximate location'],
            ],
        ];
    }
}
