<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Municipality;
use App\Models\Office;
use App\Models\Service;
use Illuminate\Database\Seeder;

class LebaneseMunicipalityServicesSeeder extends Seeder
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

        $municipalities = [
            'Municipality of Beirut' => [
                'office' => [
                    'name' => 'Beirut Civil Registry Office',
                    'address' => 'Beirut Municipality Building, Weygand Street, Beirut',
                    'latitude' => 33.89592000,
                    'longitude' => 35.50190000,
                    'contact_info' => '+961 1 987 654',
                ],
                'categories' => [
                    'Civil Status' => [
                        [
                            'name' => 'Individual Civil Status Record',
                            'description' => 'Request an official individual civil status record for use in public administration, school, employment, or legal files.',
                            'price' => 2.00,
                            'duration_days' => 3,
                            'required_documents' => ['Lebanese ID or passport copy', 'Family civil registry extract copy if available'],
                        ],
                        [
                            'name' => 'Family Civil Status Record',
                            'description' => 'Request a family civil status record issued through the municipality civil registry desk.',
                            'price' => 3.00,
                            'duration_days' => 4,
                            'required_documents' => ['Applicant Lebanese ID copy', 'Family registry number or mukhtar statement'],
                        ],
                        [
                            'name' => 'Proof of Residence Certificate',
                            'description' => 'Apply for a municipal proof of residence certificate for residents within the municipal boundaries.',
                            'price' => 5.00,
                            'duration_days' => 2,
                            'required_documents' => ['Lebanese ID or passport copy', 'Rental contract, property deed, or electricity bill', 'Mukhtar residence statement'],
                        ],
                    ],
                ],
            ],
            'Municipality of Baabda' => [
                'office' => [
                    'name' => 'Baabda Urban Planning and Permits Office',
                    'address' => 'Baabda Municipal Building, Baabda Main Road',
                    'latitude' => 33.83365000,
                    'longitude' => 35.54436000,
                    'contact_info' => '+961 5 922 410',
                ],
                'categories' => [
                    'Building and Planning' => [
                        [
                            'name' => 'Building Permit File Submission',
                            'description' => 'Submit a preliminary building permit file for municipal technical review before final authorization.',
                            'price' => 75.00,
                            'duration_days' => 15,
                            'required_documents' => ['Property deed or real estate certificate', 'Architectural plans signed by an engineer', 'Syndicate engineer authorization', 'Owner ID copy'],
                        ],
                        [
                            'name' => 'Occupancy Certificate Request',
                            'description' => 'Request a municipal occupancy certificate after construction completion and technical inspection.',
                            'price' => 50.00,
                            'duration_days' => 10,
                            'required_documents' => ['Approved building permit', 'Engineer completion report', 'Recent site photos', 'Owner ID copy'],
                        ],
                        [
                            'name' => 'Road Occupancy Permission',
                            'description' => 'Apply for temporary use of a sidewalk or public road area for works, scaffolding, or loading activity.',
                            'price' => 25.00,
                            'duration_days' => 5,
                            'required_documents' => ['Applicant ID copy', 'Location map', 'Work description and dates', 'Public safety plan if required'],
                        ],
                    ],
                ],
            ],
            'Municipality of Jounieh' => [
                'office' => [
                    'name' => 'Jounieh Business Licensing Office',
                    'address' => 'Jounieh Municipality, Fouad Chehab Street, Jounieh',
                    'latitude' => 33.98083000,
                    'longitude' => 35.61778000,
                    'contact_info' => '+961 9 830 550',
                ],
                'categories' => [
                    'Licenses and Declarations' => [
                        [
                            'name' => 'Commercial Shop License',
                            'description' => 'Apply for a municipal commercial shop license for retail, office, workshop, or hospitality activity.',
                            'price' => 40.00,
                            'duration_days' => 12,
                            'required_documents' => ['Owner or manager ID copy', 'Commercial circular or registration certificate', 'Lease contract or property deed', 'Shop photos', 'Fire safety clearance when applicable'],
                        ],
                        [
                            'name' => 'Advertisement Sign Permit',
                            'description' => 'Request approval for installing or renewing a storefront advertisement sign.',
                            'price' => 20.00,
                            'duration_days' => 6,
                            'required_documents' => ['Applicant ID copy', 'Shop license copy', 'Sign design with dimensions', 'Front elevation photo'],
                        ],
                        [
                            'name' => 'Event Notification and Municipal Approval',
                            'description' => 'Submit an event notice for a public cultural, community, or commercial event requiring municipal coordination.',
                            'price' => 15.00,
                            'duration_days' => 7,
                            'required_documents' => ['Organizer ID copy', 'Event program', 'Venue approval or rental agreement', 'Security and cleaning plan'],
                        ],
                    ],
                ],
            ],
            'Municipality of Zahle' => [
                'office' => [
                    'name' => 'Zahle Public Works and Sanitation Office',
                    'address' => 'Zahle Municipality, Boulevard Zahle, Bekaa',
                    'latitude' => 33.84675000,
                    'longitude' => 35.90203000,
                    'contact_info' => '+961 8 821 110',
                ],
                'categories' => [
                    'Public Works' => [
                        [
                            'name' => 'Road Damage or Pothole Repair Request',
                            'description' => 'Report road damage, potholes, or sidewalk hazards for inspection and municipal works scheduling.',
                            'price' => 0.00,
                            'duration_days' => 7,
                            'required_documents' => ['Location details or map pin', 'Photos of the damage', 'Reporter contact information'],
                        ],
                        [
                            'name' => 'Street Lighting Maintenance Request',
                            'description' => 'Report a broken or unsafe public street light within the municipal area.',
                            'price' => 0.00,
                            'duration_days' => 5,
                            'required_documents' => ['Location details or nearest landmark', 'Photo of the pole or street segment'],
                        ],
                        [
                            'name' => 'Large Waste Collection Request',
                            'description' => 'Request municipal coordination for collecting large household waste items from an approved pickup location.',
                            'price' => 10.00,
                            'duration_days' => 4,
                            'required_documents' => ['Applicant ID copy', 'Pickup address', 'Photos or list of items'],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($municipalities as $municipalityName => $municipalityData) {
            $municipality = Municipality::firstOrCreate(['name' => $municipalityName]);
            $officeData = $municipalityData['office'];

            $office = Office::updateOrCreate(
                ['municipality_id' => $municipality->id],
                [
                    'name' => $officeData['name'],
                    'address' => $officeData['address'],
                    'latitude' => $officeData['latitude'],
                    'longitude' => $officeData['longitude'],
                    'working_hours' => $workingHours,
                    'contact_info' => $officeData['contact_info'],
                ]
            );

            foreach ($municipalityData['categories'] as $categoryName => $services) {
                $category = Category::firstOrCreate([
                    'office_id' => $office->id,
                    'name' => $categoryName,
                ]);

                foreach ($services as $serviceData) {
                    Service::updateOrCreate(
                        [
                            'office_id' => $office->id,
                            'name' => $serviceData['name'],
                        ],
                        [
                            'category_id' => $category->id,
                            'description' => $serviceData['description'],
                            'price' => $serviceData['price'],
                            'duration_days' => $serviceData['duration_days'],
                            'required_documents' => $serviceData['required_documents'],
                        ]
                    );
                }
            }
        }

        $this->backfillExistingOffices($workingHours);
    }

    private function backfillExistingOffices(array $workingHours): void
    {
        Office::query()
            ->with(['categories.services'])
            ->get()
            ->each(function (Office $office) use ($workingHours): void {
                $hasServices = $office->categories
                    ->contains(fn (Category $category) => $category->services->isNotEmpty());

                if ($hasServices) {
                    return;
                }

                if (empty($office->working_hours)) {
                    $office->update(['working_hours' => $workingHours]);
                }

                $category = Category::firstOrCreate([
                    'office_id' => $office->id,
                    'name' => 'Municipal Services',
                ]);

                foreach ($this->defaultServicesForOffice($office) as $serviceData) {
                    Service::updateOrCreate(
                        [
                            'office_id' => $office->id,
                            'name' => $serviceData['name'],
                        ],
                        [
                            'category_id' => $category->id,
                            'description' => $serviceData['description'],
                            'price' => $serviceData['price'],
                            'duration_days' => $serviceData['duration_days'],
                            'required_documents' => $serviceData['required_documents'],
                        ]
                    );
                }
            });
    }

    private function defaultServicesForOffice(Office $office): array
    {
        $officeName = strtolower($office->name);

        if (str_contains($officeName, 'baabda') || str_contains($officeName, 'planning') || str_contains($officeName, 'permit')) {
            return [
                [
                    'name' => 'Building Permit File Submission',
                    'description' => 'Submit a preliminary building permit file for municipal technical review before final authorization.',
                    'price' => 75.00,
                    'duration_days' => 15,
                    'required_documents' => ['Property deed or real estate certificate', 'Architectural plans signed by an engineer', 'Syndicate engineer authorization', 'Owner ID copy'],
                ],
                [
                    'name' => 'Occupancy Certificate Request',
                    'description' => 'Request a municipal occupancy certificate after construction completion and technical inspection.',
                    'price' => 50.00,
                    'duration_days' => 10,
                    'required_documents' => ['Approved building permit', 'Engineer completion report', 'Recent site photos', 'Owner ID copy'],
                ],
                [
                    'name' => 'Road Occupancy Permission',
                    'description' => 'Apply for temporary use of a sidewalk or public road area for works, scaffolding, or loading activity.',
                    'price' => 25.00,
                    'duration_days' => 5,
                    'required_documents' => ['Applicant ID copy', 'Location map', 'Work description and dates', 'Public safety plan if required'],
                ],
            ];
        }

        return [
            [
                'name' => 'Proof of Residence Certificate',
                'description' => 'Apply for a municipal proof of residence certificate for residents within the municipal boundaries.',
                'price' => 5.00,
                'duration_days' => 2,
                'required_documents' => ['Lebanese ID or passport copy', 'Rental contract, property deed, or electricity bill', 'Mukhtar residence statement'],
            ],
            [
                'name' => 'Commercial Shop License',
                'description' => 'Apply for a municipal commercial shop license for retail, office, workshop, or hospitality activity.',
                'price' => 40.00,
                'duration_days' => 12,
                'required_documents' => ['Owner or manager ID copy', 'Commercial circular or registration certificate', 'Lease contract or property deed', 'Shop photos'],
            ],
            [
                'name' => 'Street Lighting Maintenance Request',
                'description' => 'Report a broken or unsafe public street light within the municipal area.',
                'price' => 0.00,
                'duration_days' => 5,
                'required_documents' => ['Location details or nearest landmark', 'Photo of the pole or street segment'],
            ],
        ];
    }
}
