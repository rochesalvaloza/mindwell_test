<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\ConsentRecord;
use App\Models\CounselingRecord;
use App\Models\CounselorAvailability;
use App\Models\CounselorProfile;
use App\Models\WellnessCategory;
use App\Models\WellnessCheckin;
use App\Models\WellnessResource;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Development seeder for the tables owned by the Laravel service.
 *
 * User ids are aligned with the Express seeder:
 *   1 admin, 2 counselor 1, 3 counselor 2,
 *   4 student 1, 5 student 2, 6 student 3.
 *
 * All names, notes and scenarios are fictional.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCounselors();
        $this->seedResources();
        $this->seedCheckins();
        $this->seedAppointments();
        $this->seedCounselingRecords();
    }

    private function seedCounselors(): void
    {
        $counselors = [
            [
                'user_id' => 2,
                'specialization' => 'Academic stress & anxiety',
                'credentials' => 'RGC, MA Psychology',
                'bio' => 'Helps students navigate academic pressure, perfectionism and test anxiety with practical, evidence-informed strategies.',
                'max_weekly_appointments' => 20,
                'slots' => [[1, '09:00', '12:00'], [3, '13:00', '16:00'], [5, '09:00', '11:00']],
            ],
            [
                'user_id' => 3,
                'specialization' => 'Relationships & family concerns',
                'credentials' => 'RGC, MS Counseling',
                'bio' => 'Supports students working through relationships, family expectations and belonging, at a pace that feels right for them.',
                'max_weekly_appointments' => 16,
                'slots' => [[2, '10:00', '15:00'], [4, '09:00', '12:00']],
            ],
        ];

        foreach ($counselors as $data) {
            $profile = CounselorProfile::query()->updateOrCreate(
                ['user_id' => $data['user_id']],
                [
                    'specialization' => $data['specialization'],
                    'credentials' => $data['credentials'],
                    'bio' => $data['bio'],
                    'max_weekly_appointments' => $data['max_weekly_appointments'],
                    'accepting_students' => true,
                ]
            );

            $profile->availability()->delete();
            foreach ($data['slots'] as [$day, $start, $end]) {
                CounselorAvailability::create([
                    'counselor_profile_id' => $profile->id,
                    'day_of_week' => $day,
                    'start_time' => $start,
                    'end_time' => $end,
                    'slot_minutes' => 50,
                    'is_active' => true,
                ]);
            }
        }
    }

    private function seedResources(): void
    {
        $categories = [
            ['name' => 'Stress & Study Balance', 'slug' => 'stress-study-balance', 'description' => 'Practical ways to study sustainably.', 'accent_color' => '#10A99B', 'sort_order' => 1],
            ['name' => 'Sleep & Rest', 'slug' => 'sleep-rest', 'description' => 'Rest as part of academic performance.', 'accent_color' => '#0B355F', 'sort_order' => 2],
            ['name' => 'Connecting with Others', 'slug' => 'connecting', 'description' => 'Friendship, belonging and asking for help.', 'accent_color' => '#52D0B0', 'sort_order' => 3],
            ['name' => 'Coping Tools', 'slug' => 'coping-tools', 'description' => 'Grounding and breathing exercises you can try.', 'accent_color' => '#7C6FBF', 'sort_order' => 4],
        ];

        $categoryMap = [];
        foreach ($categories as $category) {
            $categoryMap[$category['slug']] = WellnessCategory::query()->updateOrCreate(
                ['slug' => $category['slug']],
                $category + ['is_active' => true]
            );
        }

        $resources = [
            [
                'category' => 'stress-study-balance',
                'title' => 'Studying in 25-minute focus blocks',
                'excerpt' => 'A gentle introduction to breaking big tasks into small, finishable pieces.',
                'body' => "Big deadlines feel lighter when they are broken down.\n\nTry this: pick one task, set a timer for 25 minutes, and work on only that task. When the timer ends, stand up, stretch, and take a 5-minute break away from your screen.\n\nAfter four blocks, take a longer break. The goal is not to work non-stop — it is to make steady progress without burning out.",
                'tags' => ['study', 'focus', 'time'],
                'reading_minutes' => 4,
                'is_featured' => true,
            ],
            [
                'category' => 'sleep-rest',
                'title' => 'Why sleep is part of studying',
                'excerpt' => 'Memory consolidates while you rest. Sleep is not the opposite of productivity.',
                'body' => "Pulling an all-nighter can feel productive, but memory and attention drop sharply the next day.\n\nAim for a consistent sleep window, dim screens in the last hour before bed, and keep your room cool and dark where possible.\n\nIf sleep struggles continue for weeks, it is worth talking to a counselor or a health professional.",
                'tags' => ['sleep', 'rest', 'memory'],
                'reading_minutes' => 5,
                'is_featured' => true,
            ],
            [
                'category' => 'connecting',
                'title' => 'How to tell a friend you are struggling',
                'excerpt' => 'You do not need the perfect words. Starting the conversation is enough.',
                'body' => "You might start with something simple: \"I have been having a rough few weeks and I wanted to tell someone.\"\n\nYou get to decide how much to share. A good friend does not need to fix anything — listening is already support.\n\nIf you are worried about someone else, ask directly and gently, and help them connect with a counselor.",
                'tags' => ['friends', 'support', 'belonging'],
                'reading_minutes' => 4,
                'is_featured' => false,
            ],
            [
                'category' => 'coping-tools',
                'title' => 'A one-minute grounding exercise',
                'excerpt' => 'A short practice you can use between classes or before an exam.',
                'body' => "Sit comfortably and let your shoulders drop.\n\nName five things you can see, four you can touch, three you can hear, two you can smell, and one you can taste.\n\nThis is not about forcing calm — it is about giving your attention somewhere steady to rest. Practice it when you are already calm so it is easier to reach for when you are not.",
                'tags' => ['grounding', 'breathing', 'anxiety'],
                'reading_minutes' => 3,
                'is_featured' => false,
            ],
            [
                'category' => 'stress-study-balance',
                'title' => 'When perfectionism gets in the way',
                'excerpt' => 'Noticing the difference between high standards and self-criticism.',
                'body' => "High standards can help you grow. Perfectionism is different: it makes finishing feel impossible and failure feel dangerous.\n\nTry asking yourself: \"What would good enough look like today?\" and writing that down before you start.\n\nIf this pattern is causing ongoing distress, a counselor can help you work through it.",
                'tags' => ['perfectionism', 'anxiety', 'study'],
                'reading_minutes' => 5,
                'is_featured' => true,
            ],
            [
                'category' => 'coping-tools',
                'title' => 'Naming what you feel',
                'excerpt' => 'Putting feelings into words can make them easier to hold.',
                'body' => "Try writing one sentence: \"Right now I feel ______ because ______.\"\n\nThere is no wrong answer. Naming an emotion does not solve it, but it can move you from overwhelmed to a little more clear.\n\nIf the same feeling keeps returning, consider sharing it with a counselor.",
                'tags' => ['emotions', 'journaling'],
                'reading_minutes' => 3,
                'is_featured' => false,
            ],
            [
                'category' => 'stress-study-balance',
                'title' => 'Managing exam anxiety',
                'excerpt' => 'Practical ways to steady your nerves before and during exams.',
                'body' => "Exam anxiety is common and manageable.\n\nBefore the exam, try a short breathing routine and review only key points rather than cramming. During the exam, if your mind goes blank, pause, breathe, and move to a question you can answer.\n\nIf anxiety regularly interferes with your performance, consider reaching out to a counselor.",
                'tags' => ['exams', 'anxiety', 'study'],
                'reading_minutes' => 4,
                'is_featured' => true,
            ],
            [
                'category' => 'coping-tools',
                'title' => 'Breathing techniques for moments of panic',
                'excerpt' => 'Simple breathing patterns you can use when stress spikes.',
                'body' => "When panic rises, your breathing often becomes fast and shallow.\n\nTry inhaling for four counts, holding for four, and exhaling for six. Repeat for a minute or two. Longer exhales signal your body to slow down.\n\nPractice this when calm so it feels familiar when you need it.",
                'tags' => ['breathing', 'panic', 'grounding'],
                'reading_minutes' => 3,
                'is_featured' => false,
            ],
            [
                'category' => 'sleep-rest',
                'title' => 'Building a sleep routine that sticks',
                'excerpt' => 'Small, consistent habits that improve sleep over time.',
                'body' => "Consistency matters more than perfection.\n\nPick a regular bedtime and wake time, limit caffeine after mid-afternoon, and keep screens out of bed where possible. If you cannot sleep after twenty minutes, get up briefly and return when sleepy.\n\nTrack your sleep for a week to notice patterns rather than judging single nights.",
                'tags' => ['sleep', 'routine', 'habits'],
                'reading_minutes' => 5,
                'is_featured' => false,
            ],
            [
                'category' => 'connecting',
                'title' => 'When and how to reach out to a counselor',
                'excerpt' => 'What to expect and how to take the first step.',
                'body' => "You do not need to be in crisis to talk to a counselor.\n\nIf something has been weighing on you for more than a couple of weeks, or you notice changes in sleep, appetite, or mood, it may help to speak with someone. Booking a first session is often the hardest part.\n\nCounselors are there to listen and work with you at your pace.",
                'tags' => ['counseling', 'help', 'support'],
                'reading_minutes' => 4,
                'is_featured' => true,
            ],
            [
                'category' => 'stress-study-balance',
                'title' => 'A gentle digital detox for students',
                'excerpt' => 'Reducing screen fatigue without disconnecting completely.',
                'body' => "Screens are part of student life, but constant notifications drain attention.\n\nTry turning off non-essential notifications, setting one phone-free hour before bed, and using a simple timer for social media. Replace scrolling with a short walk or a chat with a friend.\n\nNotice how your focus changes over a few days rather than aiming for perfection.",
                'tags' => ['digital', 'focus', 'wellbeing'],
                'reading_minutes' => 4,
                'is_featured' => false,
            ],
            [
                'category' => 'coping-tools',
                'title' => 'Mindful walking between classes',
                'excerpt' => 'Turn short walks into a reset for your body and mind.',
                'body' => "Walking can be more than getting from one place to another.\n\nOn your next short walk, notice your footsteps, your breath, and what you see around you. When your mind wanders, gently bring it back.\n\nEven two minutes of mindful walking can help you arrive more settled.",
                'tags' => ['mindfulness', 'movement', 'stress'],
                'reading_minutes' => 3,
                'is_featured' => false,
            ],
            [
                'category' => 'connecting',
                'title' => 'Healthy boundaries in group work',
                'excerpt' => 'Collaborate well without carrying everything alone.',
                'body' => "Group projects can create stress when responsibilities are unclear.\n\nAgree early on who does what and by when, and check in regularly. It is okay to say you are at capacity and ask for help or a redistribution of tasks.\n\nClear boundaries usually make the work smoother for everyone.",
                'tags' => ['groups', 'boundaries', 'collaboration'],
                'reading_minutes' => 4,
                'is_featured' => false,
            ],
            [
                'category' => 'sleep-rest',
                'title' => 'How food and mood connect',
                'excerpt' => 'Gentle nutrition habits that support steady energy.',
                'body' => "Food affects how you feel and think.\n\nAim for regular meals with protein, fibre, and fluids. Skipping meals can worsen irritability and fatigue. You do not need a perfect diet—small, consistent choices help.\n\nIf you notice strong patterns between eating and mood, a professional can help you explore them.",
                'tags' => ['nutrition', 'energy', 'mood'],
                'reading_minutes' => 4,
                'is_featured' => false,
            ],
            [
                'category' => 'connecting',
                'title' => 'Coping with homesickness at university',
                'excerpt' => 'Feeling out of place is normal, and it can ease with time.',
                'body' => "Homesickness is a sign of care for the people and places you left.\n\nBuild small routines, stay in touch with home, and give yourself time to settle. Joining one club or study group can help you find belonging.\n\nIf homesickness persists or deepens, talking to a counselor can make a real difference.",
                'tags' => ['homesickness', 'belonging', 'transition'],
                'reading_minutes' => 5,
                'is_featured' => false,
            ],
            [
                'category' => 'stress-study-balance',
                'title' => 'Setting realistic goals for the semester',
                'excerpt' => 'Plan in a way that supports progress without burnout.',
                'body' => "Big goals are easier when broken into weekly steps.\n\nChoose one or two priorities per week, schedule rest alongside study, and review what worked. Adjust goals when life changes rather than abandoning them.\n\nProgress, not perfection, is what carries you through a semester.",
                'tags' => ['goals', 'planning', 'study'],
                'reading_minutes' => 4,
                'is_featured' => true,
            ],
        ];

        foreach ($resources as $i => $resource) {
            $category = $categoryMap[$resource['category']];
            $slug = \Illuminate\Support\Str::slug($resource['title']);
            WellnessResource::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'wellness_category_id' => $category->id,
                    'title' => $resource['title'],
                    'excerpt' => $resource['excerpt'],
                    'body' => $resource['body'],
                    'tags' => $resource['tags'],
                    'reading_minutes' => $resource['reading_minutes'],
                    'status' => WellnessResource::STATUS_PUBLISHED,
                    'is_featured' => $resource['is_featured'],
                    'published_at' => now()->subDays($i),
                    'created_by_user_id' => 1,
                ]
            );
        }
    }

    private function seedCheckins(): void
    {
        // One week of fictional check-ins for student 1 (mood 3,2,3,4,4,5,4).
        $moods = [3, 2, 3, 4, 4, 5, 4];
        $influences = [
            ['study', 'sleep'],
            ['deadlines', 'family'],
            ['study'],
            ['friends', 'exercise'],
            ['rest'],
            ['friends', 'rest'],
            ['study', 'exercise'],
        ];
        $notes = [
            'Managed to finish a chapter today.',
            'A lot due this week. Feeling stretched.',
            'Steady day, nothing dramatic.',
            'Studied with friends — helped a lot.',
            'Rested more than I planned. Needed it.',
            'Good day with family.',
            'Feeling more like myself.',
        ];

        if (WellnessCheckin::query()->where('student_user_id', 4)->exists()) {
            return;
        }

        foreach ($moods as $i => $mood) {
            $created = Carbon::now()->subDays(6 - $i)->setTime(19, 30);
            WellnessCheckin::create([
                'student_user_id' => 4,
                'mood_score' => $mood,
                'energy_score' => min(5, max(1, $mood)),
                'stress_score' => 6 - $mood,
                'sleep_hours' => 6 + ($mood > 3 ? 1.5 : 0),
                'influences' => $influences[$i],
                'note' => $notes[$i],
                'is_shared_with_counselor' => false,
                'created_at' => $created,
                'updated_at' => $created,
            ]);
        }

        ConsentRecord::query()->updateOrCreate(
            ['student_user_id' => 4, 'consent_type' => ConsentRecord::TYPE_CHECKIN_SHARING],
            ['is_granted' => false, 'policy_version' => 'v1']
        );
        ConsentRecord::query()->updateOrCreate(
            ['student_user_id' => 4, 'consent_type' => ConsentRecord::TYPE_RESEARCH],
            ['is_granted' => false, 'policy_version' => 'v1']
        );
    }

    private function seedAppointments(): void
    {
        $counselor1 = CounselorProfile::query()->where('user_id', 2)->first();
        $counselor2 = CounselorProfile::query()->where('user_id', 3)->first();

        if (! $counselor1 || ! $counselor2) {
            return;
        }

        $rows = [
            // Upcoming, confirmed (student 1 with counselor 1)
            [
                'student_user_id' => 4,
                'counselor_profile_id' => $counselor1->id,
                'slot_start' => Carbon::now()->addDays(2)->setTime(10, 0),
                'status' => Appointment::STATUS_CONFIRMED,
                'mode' => 'online',
                'concern_category' => 'Academic stress',
                'student_note' => 'Feeling behind on requirements and struggling to start tasks.',
            ],
            // Pending request (student 2 with counselor 1)
            [
                'student_user_id' => 5,
                'counselor_profile_id' => $counselor1->id,
                'slot_start' => Carbon::now()->addDays(3)->setTime(14, 0),
                'status' => Appointment::STATUS_PENDING,
                'mode' => 'in_person',
                'concern_category' => 'Exam anxiety',
                'student_note' => 'I have been getting very anxious before exams.',
            ],
            // Pending request (student 3 with counselor 2)
            [
                'student_user_id' => 6,
                'counselor_profile_id' => $counselor2->id,
                'slot_start' => Carbon::now()->addDays(1)->setTime(11, 0),
                'status' => Appointment::STATUS_PENDING,
                'mode' => 'online',
                'concern_category' => 'Family concerns',
                'student_note' => 'Some things at home have been hard to talk about.',
            ],
            // Completed (for caseload + records)
            [
                'student_user_id' => 4,
                'counselor_profile_id' => $counselor1->id,
                'slot_start' => Carbon::now()->subDays(9)->setTime(10, 0),
                'status' => Appointment::STATUS_COMPLETED,
                'mode' => 'online',
                'concern_category' => 'Academic stress',
                'student_note' => 'First session.',
            ],
            [
                'student_user_id' => 5,
                'counselor_profile_id' => $counselor1->id,
                'slot_start' => Carbon::now()->subDays(16)->setTime(15, 0),
                'status' => Appointment::STATUS_COMPLETED,
                'mode' => 'in_person',
                'concern_category' => 'Exam anxiety',
            ],
            // Cancelled example
            [
                'student_user_id' => 6,
                'counselor_profile_id' => $counselor2->id,
                'slot_start' => Carbon::now()->subDays(4)->setTime(13, 0),
                'status' => Appointment::STATUS_CANCELLED,
                'mode' => 'online',
                'cancellation_reason' => 'Schedule conflict',
                'cancelled_by_user_id' => 6,
                'cancelled_at' => Carbon::now()->subDays(5),
            ],
        ];

        foreach ($rows as $row) {
            $start = $row['slot_start'];
            Appointment::query()->updateOrCreate(
                [
                    'counselor_profile_id' => $row['counselor_profile_id'],
                    'slot_start' => $start,
                ],
                [
                    ...$row,
                    'slot_end' => (clone $start)->addMinutes(50),
                    'confirmed_at' => $row['status'] === Appointment::STATUS_CONFIRMED ? now() : null,
                    'completed_at' => $row['status'] === Appointment::STATUS_COMPLETED ? $start->copy()->addMinutes(50) : null,
                ]
            );
        }
    }

    private function seedCounselingRecords(): void
    {
        if (CounselingRecord::query()->exists()) {
            return;
        }

        $appointment = Appointment::query()
            ->where('student_user_id', 4)
            ->where('status', Appointment::STATUS_COMPLETED)
            ->first();

        CounselingRecord::create([
            'student_user_id' => 4,
            'counselor_user_id' => 2,
            'appointment_id' => $appointment?->id,
            'session_date' => Carbon::now()->subDays(9)->toDateString(),
            'summary' => 'Initial check-in. Student described difficulty starting tasks and feeling behind on requirements. Explored current routines and identified two realistic weekly goals.',
            'follow_up_plan' => 'Review progress in two weeks. Suggested a short daily planning habit.',
            'recommendations' => 'Introduced 25-minute focus blocks and a wind-down routine before bed.',
            'referral_service' => null,
            'risk_level' => CounselingRecord::RISK_NONE,
        ]);
    }
}
