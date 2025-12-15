<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import {
    ArchiveX,
    Briefcase,
    Building2,
    Calendar,
    ChevronLeft,
    DollarSign,
    Edit,
    Eye,
    FileText,
    Globe,
    LockKeyhole,
    LockKeyholeOpen,
    Mail,
    Monitor,
    Smartphone,
    Sparkles,
    Star,
    Tablet,
    Users,
    Zap,
} from 'lucide-vue-next';
import hr from '@/routes/hr';
import positions from '@/routes/positions';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';

interface Technology {
    id: number;
    name: string;
}

interface Company {
    id: number;
    name: string;
    logo_path: string | null;
}

interface CustomQuestion {
    id: number;
    question_text: string;
    is_required: boolean;
}

interface User {
    id: number;
    name: string;
    email: string;
    developer_profile?: {
        summary: string | null;
        cv_path: string | null;
        github_url: string | null;
        linkedin_url: string | null;
        portfolio_url: string | null;
    } | null;
}

interface Application {
    id: number;
    user: User;
    status: 'pending' | 'reviewing' | 'accepted' | 'rejected';
    applied_at: string;
    user_archived: boolean;
}

interface Position {
    id: number;
    title: string;
    slug: string;
    short_description: string;
    long_description: string;
    seniority: 'junior' | 'mid' | 'senior' | 'lead' | 'principal';
    salary_min: number | null;
    salary_max: number | null;
    remote_type: 'global' | 'timezone' | 'country';
    location_restriction: string | null;
    status: 'draft' | 'published' | 'closed';
    listing_type: string;
    is_external: boolean;
    external_url: string | null;
    allow_platform_applications: boolean;
    published_at: string | null;
    created_at: string;
    company: Company;
    technologies: Technology[];
    custom_questions: CustomQuestion[];
    applications: Application[];
    applications_count: number;
    views_count: number;
    creator: User;
}

interface ApplicationStats {
    total: number;
    pending: number;
    reviewing: number;
    accepted: number;
    rejected: number;
}

interface Analytics {
    total_views: number;
    countries: Array<{ country: string; count: number }>;
    devices: Array<{
        device: string;
        device_type: string;
        browser: string | null;
        os: string | null;
        count: number;
    }>;
    views_by_date: Array<{ date: string; count: number }>;
}

interface UpgradeOption {
    tier: string;
    label: string;
    price: number;
    remaining_days: number | null;
}

const props = defineProps<{
    position: Position;
    applicationStats: ApplicationStats;
    analytics: Analytics;
    upgradeOptions?: Record<string, UpgradeOption>;
    pricing?: {
        regular: number;
        featured: number;
        top: number;
    };
}>();

const activeTab = ref('all');
const showCloseConfirmDialog = ref(false);

const filteredApplications = computed(() => {
    if (activeTab.value === 'all') {
        return props.position.applications;
    }
    return props.position.applications.filter((app) => app.status === activeTab.value);
});

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
    });
};

const formatSalary = (min: number | null, max: number | null) => {
    if (!min && !max) {
        return 'Not specified';
    }
    const formatter = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 0,
    });
    if (min && max) {
        return `${formatter.format(min)} - ${formatter.format(max)}`;
    }
    return formatter.format(min || max || 0);
};

const getStatusColor = (status: string) => {
    const colors = {
        draft: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        published: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        closed: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    };
    return colors[status as keyof typeof colors] || colors.draft;
};

const getSeniorityLabel = (seniority: string) => {
    const labels = {
        junior: 'Junior',
        mid: 'Mid-Level',
        senior: 'Senior',
        lead: 'Lead',
        principal: 'Principal',
    };
    return labels[seniority as keyof typeof labels] || seniority;
};

const getLocationLabel = (remoteType: string, locationRestriction: string | null) => {
    if (remoteType === 'global') {
        return 'Global';
    }
    if (locationRestriction) {
        return locationRestriction;
    }
    return remoteType === 'timezone' ? 'Timezone Restricted' : 'Country Restricted';
};

const getApplicationStatusColor = (status: string) => {
    const colors = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        reviewing: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        accepted: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        rejected: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    };
    return colors[status as keyof typeof colors] || colors.pending;
};

const rejectApplication = (applicationId: number) => {
    const form = useForm({
        status: 'rejected',
        _method: 'PATCH',
    });

    form.post(hr.applications.update(applicationId).url, {
        preserveScroll: true,
        onSuccess: () => {
            // Refresh the position data
            location.reload();
        },
    });
};

const toggleApplications = () => {
    // If closing applications, show confirmation dialog
    if (props.position.allow_platform_applications) {
        showCloseConfirmDialog.value = true;
        return;
    }

    // If opening applications, proceed directly
    performToggle();
};

const performToggle = () => {
    const form = useForm({});

    form.post(hr.positions.toggleApplications(props.position.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            showCloseConfirmDialog.value = false;
            location.reload();
        },
        onError: () => {
            toast.error('There was an error toggling applications. Please try again.');
        },
    });
};

const upgradeTier = (tier: string) => {
    const form = useForm({
        tier,
    });

    form.post(hr.positions.payment.upgrade(props.position.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Upgrade initiated. Redirecting to payment...');
        },
        onError: () => {
            toast.error('There was an error processing the upgrade. Please try again.');
        },
    });
};

const getDeviceIcon = (device: string) => {
    const icons = {
        mobile: Smartphone,
        tablet: Tablet,
        desktop: Monitor,
        bot: Globe,
        unknown: Monitor,
    };
    return icons[device as keyof typeof icons] || Monitor;
};

const getCountryFlag = (countryCode: string) => {
    if (!countryCode || countryCode.length !== 2) {
        return '🌍'; // World emoji as fallback
    }
    
    // Convert country code to flag emoji
    // Each letter is converted to its regional indicator symbol
    const codePoints = countryCode
        .toUpperCase()
        .split('')
        .map((char) => 127397 + char.charCodeAt(0));
    
    return String.fromCodePoint(...codePoints);
};

const getCountryName = (countryCode: string) => {
    const countries: Record<string, string> = {
        'AD': 'Andorra',
        'AE': 'United Arab Emirates',
        'AF': 'Afghanistan',
        'AG': 'Antigua and Barbuda',
        'AI': 'Anguilla',
        'AL': 'Albania',
        'AM': 'Armenia',
        'AO': 'Angola',
        'AQ': 'Antarctica',
        'AR': 'Argentina',
        'AS': 'American Samoa',
        'AT': 'Austria',
        'AU': 'Australia',
        'AW': 'Aruba',
        'AX': 'Aland Islands',
        'AZ': 'Azerbaijan',
        'BA': 'Bosnia and Herzegovina',
        'BB': 'Barbados',
        'BD': 'Bangladesh',
        'BE': 'Belgium',
        'BF': 'Burkina Faso',
        'BG': 'Bulgaria',
        'BH': 'Bahrain',
        'BI': 'Burundi',
        'BJ': 'Benin',
        'BL': 'Saint Barthelemy',
        'BM': 'Bermuda',
        'BN': 'Brunei',
        'BO': 'Bolivia',
        'BQ': 'Caribbean Netherlands',
        'BR': 'Brazil',
        'BS': 'Bahamas',
        'BT': 'Bhutan',
        'BV': 'Bouvet Island',
        'BW': 'Botswana',
        'BY': 'Belarus',
        'BZ': 'Belize',
        'CA': 'Canada',
        'CC': 'Cocos Islands',
        'CD': 'DR Congo',
        'CF': 'Central African Republic',
        'CG': 'Congo',
        'CH': 'Switzerland',
        'CI': 'Cote d\'Ivoire',
        'CK': 'Cook Islands',
        'CL': 'Chile',
        'CM': 'Cameroon',
        'CN': 'China',
        'CO': 'Colombia',
        'CR': 'Costa Rica',
        'CU': 'Cuba',
        'CV': 'Cape Verde',
        'CW': 'Curacao',
        'CX': 'Christmas Island',
        'CY': 'Cyprus',
        'CZ': 'Czechia',
        'DE': 'Germany',
        'DJ': 'Djibouti',
        'DK': 'Denmark',
        'DM': 'Dominica',
        'DO': 'Dominican Republic',
        'DZ': 'Algeria',
        'EC': 'Ecuador',
        'EE': 'Estonia',
        'EG': 'Egypt',
        'EH': 'Western Sahara',
        'ER': 'Eritrea',
        'ES': 'Spain',
        'ET': 'Ethiopia',
        'FI': 'Finland',
        'FJ': 'Fiji',
        'FK': 'Falkland Islands',
        'FM': 'Micronesia',
        'FO': 'Faroe Islands',
        'FR': 'France',
        'GA': 'Gabon',
        'GB': 'United Kingdom',
        'GD': 'Grenada',
        'GE': 'Georgia',
        'GF': 'French Guiana',
        'GG': 'Guernsey',
        'GH': 'Ghana',
        'GI': 'Gibraltar',
        'GL': 'Greenland',
        'GM': 'Gambia',
        'GN': 'Guinea',
        'GP': 'Guadeloupe',
        'GQ': 'Equatorial Guinea',
        'GR': 'Greece',
        'GS': 'South Georgia',
        'GT': 'Guatemala',
        'GU': 'Guam',
        'GW': 'Guinea-Bissau',
        'GY': 'Guyana',
        'HK': 'Hong Kong',
        'HM': 'Heard Island',
        'HN': 'Honduras',
        'HR': 'Croatia',
        'HT': 'Haiti',
        'HU': 'Hungary',
        'ID': 'Indonesia',
        'IE': 'Ireland',
        'IL': 'Israel',
        'IM': 'Isle of Man',
        'IN': 'India',
        'IO': 'British Indian Ocean Territory',
        'IQ': 'Iraq',
        'IR': 'Iran',
        'IS': 'Iceland',
        'IT': 'Italy',
        'JE': 'Jersey',
        'JM': 'Jamaica',
        'JO': 'Jordan',
        'JP': 'Japan',
        'KE': 'Kenya',
        'KG': 'Kyrgyzstan',
        'KH': 'Cambodia',
        'KI': 'Kiribati',
        'KM': 'Comoros',
        'KN': 'Saint Kitts and Nevis',
        'KP': 'North Korea',
        'KR': 'South Korea',
        'KW': 'Kuwait',
        'KY': 'Cayman Islands',
        'KZ': 'Kazakhstan',
        'LA': 'Laos',
        'LB': 'Lebanon',
        'LC': 'Saint Lucia',
        'LI': 'Liechtenstein',
        'LK': 'Sri Lanka',
        'LR': 'Liberia',
        'LS': 'Lesotho',
        'LT': 'Lithuania',
        'LU': 'Luxembourg',
        'LV': 'Latvia',
        'LY': 'Libya',
        'MA': 'Morocco',
        'MC': 'Monaco',
        'MD': 'Moldova',
        'ME': 'Montenegro',
        'MF': 'Saint Martin',
        'MG': 'Madagascar',
        'MH': 'Marshall Islands',
        'MK': 'North Macedonia',
        'ML': 'Mali',
        'MM': 'Myanmar',
        'MN': 'Mongolia',
        'MO': 'Macao',
        'MP': 'Northern Mariana Islands',
        'MQ': 'Martinique',
        'MR': 'Mauritania',
        'MS': 'Montserrat',
        'MT': 'Malta',
        'MU': 'Mauritius',
        'MV': 'Maldives',
        'MW': 'Malawi',
        'MX': 'Mexico',
        'MY': 'Malaysia',
        'MZ': 'Mozambique',
        'NA': 'Namibia',
        'NC': 'New Caledonia',
        'NE': 'Niger',
        'NF': 'Norfolk Island',
        'NG': 'Nigeria',
        'NI': 'Nicaragua',
        'NL': 'Netherlands',
        'NO': 'Norway',
        'NP': 'Nepal',
        'NR': 'Nauru',
        'NU': 'Niue',
        'NZ': 'New Zealand',
        'OM': 'Oman',
        'PA': 'Panama',
        'PE': 'Peru',
        'PF': 'French Polynesia',
        'PG': 'Papua New Guinea',
        'PH': 'Philippines',
        'PK': 'Pakistan',
        'PL': 'Poland',
        'PM': 'Saint Pierre and Miquelon',
        'PN': 'Pitcairn Islands',
        'PR': 'Puerto Rico',
        'PS': 'Palestine',
        'PT': 'Portugal',
        'PW': 'Palau',
        'PY': 'Paraguay',
        'QA': 'Qatar',
        'RE': 'Reunion',
        'RO': 'Romania',
        'RS': 'Serbia',
        'RU': 'Russia',
        'RW': 'Rwanda',
        'SA': 'Saudi Arabia',
        'SB': 'Solomon Islands',
        'SC': 'Seychelles',
        'SD': 'Sudan',
        'SE': 'Sweden',
        'SG': 'Singapore',
        'SH': 'Saint Helena',
        'SI': 'Slovenia',
        'SJ': 'Svalbard and Jan Mayen',
        'SK': 'Slovakia',
        'SL': 'Sierra Leone',
        'SM': 'San Marino',
        'SN': 'Senegal',
        'SO': 'Somalia',
        'SR': 'Suriname',
        'SS': 'South Sudan',
        'ST': 'Sao Tome and Principe',
        'SV': 'El Salvador',
        'SX': 'Sint Maarten',
        'SY': 'Syria',
        'SZ': 'Eswatini',
        'TC': 'Turks and Caicos Islands',
        'TD': 'Chad',
        'TF': 'French Southern Territories',
        'TG': 'Togo',
        'TH': 'Thailand',
        'TJ': 'Tajikistan',
        'TK': 'Tokelau',
        'TL': 'Timor-Leste',
        'TM': 'Turkmenistan',
        'TN': 'Tunisia',
        'TO': 'Tonga',
        'TR': 'Turkey',
        'TT': 'Trinidad and Tobago',
        'TV': 'Tuvalu',
        'TW': 'Taiwan',
        'TZ': 'Tanzania',
        'UA': 'Ukraine',
        'UG': 'Uganda',
        'UM': 'U.S. Minor Outlying Islands',
        'US': 'United States',
        'UY': 'Uruguay',
        'UZ': 'Uzbekistan',
        'VA': 'Vatican City',
        'VC': 'Saint Vincent and the Grenadines',
        'VE': 'Venezuela',
        'VG': 'British Virgin Islands',
        'VI': 'U.S. Virgin Islands',
        'VN': 'Vietnam',
        'VU': 'Vanuatu',
        'WF': 'Wallis and Futuna',
        'WS': 'Samoa',
        'XK': 'Kosovo*',
        'YE': 'Yemen',
        'YT': 'Mayotte',
        'ZA': 'South Africa',
        'ZM': 'Zambia',
        'ZW': 'Zimbabwe',
    };

    return countries[countryCode.toUpperCase()] || countryCode;
};

const breadcrumbs = [
    {
        title: 'HR Dashboard',
        href: hr.dashboard().url,
    },
    {
        title: 'Positions',
        href: hr.positions.index().url,
    },
    {
        title: props.position.title,
        href: hr.positions.show(props.position.id).url,
    },
];
</script>

<template>
    <Head :title="position.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="hr.positions.index().url">
                        <Button variant="ghost" size="sm">
                            <ChevronLeft class="mr-2 h-4 w-4" />
                            Back
                        </Button>
                    </Link>
                    <h1 class="text-2xl font-bold">{{ position.title }}</h1>
                </div>
                <div class="flex items-center gap-2">
                    <a 
                        v-if="position.status === 'published'"
                        :href="positions.show(position.slug).url" 
                        target="_blank"
                    >
                        <Button variant="outline" size="sm">
                            <Eye class="mr-2 h-4 w-4" />
                            View Public Page
                        </Button>
                    </a>
                    <a 
                        v-else
                        :href="hr.positions.preview(position.id).url" 
                        target="_blank"
                    >
                        <Button variant="outline" size="sm">
                            <Eye class="mr-2 h-4 w-4" />
                            Preview
                        </Button>
                    </a>
                    <Link :href="hr.positions.edit(position.id).url">
                        <Button size="sm">
                            <Edit class="mr-2 h-4 w-4" />
                            Edit
                        </Button>
                    </Link>
                    <Button 
                        v-if="!position.is_external" 
                        size="sm" 
                        :variant="position.allow_platform_applications ? 'destructive' : 'default'"
                        @click="toggleApplications"
                    >
                        <LockKeyhole v-if="position.allow_platform_applications" class="mr-2 h-4 w-4" />
                        <LockKeyholeOpen v-else class="mr-2 h-4 w-4" />
                        {{ position.allow_platform_applications ? 'Close Applications' : 'Open Applications' }}
                    </Button>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Position Details -->
                    <Card>
                        <CardHeader>
                            <div class="flex items-start justify-between">
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <Badge :class="getStatusColor(position.status)">
                                            {{ position.status }}
                                        </Badge>
                                        <Badge v-if="position.listing_type === 'top'" variant="secondary">
                                            ⭐ Top
                                        </Badge>
                                        <Badge v-else-if="position.listing_type === 'featured'" variant="secondary">
                                            Featured
                                        </Badge>
                                        <Badge
                                            v-if="position.is_external"
                                            variant="outline"
                                            class="border-gray-300 text-gray-700 dark:border-gray-700 dark:text-gray-300"
                                        >
                                            External
                                        </Badge>
                                    </div>
                                    <CardTitle class="text-2xl">{{ position.title }}</CardTitle>
                                    <CardDescription class="flex items-center gap-2">
                                        <Building2 class="h-4 w-4" />
                                        {{ position.company.name }}
                                    </CardDescription>
                                </div>
                            </div>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <!-- Meta Information -->
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                    <Briefcase class="h-4 w-4" />
                                    <span>{{ getSeniorityLabel(position.seniority) }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                    <Globe class="h-4 w-4" />
                                    <span>{{ getLocationLabel(position.remote_type, position.location_restriction) }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                    <DollarSign class="h-4 w-4" />
                                    <span>{{ formatSalary(position.salary_min, position.salary_max) }}</span>
                                </div>
                            </div>

                            <Separator />

                            <!-- Short Description -->
                            <div>
                                <h3 class="mb-2 font-semibold text-gray-900 dark:text-gray-100">Overview</h3>
                                <p class="text-gray-700 dark:text-gray-300">
                                    {{ position.short_description }}
                                </p>
                            </div>

                            <Separator />

                            <!-- Long Description -->
                            <div>
                                <h3 class="mb-2 font-semibold text-gray-900 dark:text-gray-100">
                                    Full Description
                                </h3>
                                <div
                                    class="prose prose-sm max-w-none text-gray-700 dark:prose-invert dark:text-gray-300"
                                    v-html="position.long_description"
                                />
                            </div>

                            <!-- Technologies -->
                            <div v-if="position.technologies.length > 0">
                                <h3 class="mb-2 font-semibold text-gray-900 dark:text-gray-100">
                                    Required Technologies
                                </h3>
                                <div class="flex flex-wrap gap-2">
                                    <Badge v-for="tech in position.technologies" :key="tech.id" variant="secondary">
                                        {{ tech.name }}
                                    </Badge>
                                </div>
                            </div>

                            <!-- Custom Questions -->
                            <div v-if="position.custom_questions.length > 0">
                                <h3 class="mb-2 font-semibold text-gray-900 dark:text-gray-100">
                                    Application Questions
                                </h3>
                                <ul class="space-y-2">
                                    <li
                                        v-for="(question, index) in position.custom_questions"
                                        :key="question.id"
                                        class="text-sm text-gray-700 dark:text-gray-300"
                                    >
                                        {{ index + 1 }}. {{ question.question_text }}
                                        <span v-if="question.is_required" class="text-red-500">*</span>
                                    </li>
                                </ul>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Applications List -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Applications</CardTitle>
                            <CardDescription>
                                {{ applicationStats.total }} total applications
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Tabs v-model="activeTab" default-value="all" class="w-full">
                                <TabsList class="grid w-full grid-cols-5">
                                    <TabsTrigger value="all">
                                        All
                                        <Badge variant="secondary" class="ml-2">
                                            {{ applicationStats.total }}
                                        </Badge>
                                    </TabsTrigger>
                                    <TabsTrigger value="pending">
                                        Pending
                                        <Badge variant="secondary" class="ml-2">
                                            {{ applicationStats.pending }}
                                        </Badge>
                                    </TabsTrigger>
                                    <TabsTrigger value="reviewing">
                                        Reviewing
                                        <Badge variant="secondary" class="ml-2">
                                            {{ applicationStats.reviewing }}
                                        </Badge>
                                    </TabsTrigger>
                                    <TabsTrigger value="accepted">
                                        Accepted
                                        <Badge variant="secondary" class="ml-2">
                                            {{ applicationStats.accepted }}
                                        </Badge>
                                    </TabsTrigger>
                                    <TabsTrigger value="rejected">
                                        Rejected
                                        <Badge variant="secondary" class="ml-2">
                                            {{ applicationStats.rejected }}
                                        </Badge>
                                    </TabsTrigger>
                                </TabsList>

                                <TabsContent value="all" class="mt-4">
                                    <div v-if="filteredApplications.length > 0" class="space-y-3">
                                        <component
                                            :is="application.user_archived ? 'div' : Link"
                                            v-for="application in filteredApplications"
                                            :key="application.id"
                                            :href="!application.user_archived ? hr.applications.show(application.id).url : undefined"
                                            :class="[
                                                'block rounded-lg border p-4 transition-colors dark:border-gray-700',
                                                application.user_archived
                                                    ? 'cursor-not-allowed opacity-60'
                                                    : 'hover:bg-muted/50'
                                            ]"
                                        >
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1 space-y-1">
                                                    <div class="flex items-center gap-2">
                                                        <p class="font-medium text-gray-900 dark:text-gray-100">
                                                            {{ application.user.name }}
                                                        </p>
                                                        <Badge :class="getApplicationStatusColor(application.status)">
                                                            {{ application.status }}
                                                        </Badge>
                                                        <Badge
                                                            v-if="application.user_archived"
                                                            class="bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                        >
                                                            <ArchiveX class="mr-1 h-3 w-3" />
                                                            Archived
                                                        </Badge>
                                                    </div>
                                                    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                                        <Mail class="h-3 w-3" />
                                                        {{ application.user.email }}
                                                    </div>
                                                    <p class="text-xs text-gray-400 dark:text-gray-500">
                                                        Applied {{ formatDate(application.applied_at) }}
                                                    </p>
                                                </div>
                                                <div v-if="application.user_archived && application.status !== 'rejected'">
                                                    <Button
                                                        size="sm"
                                                        variant="ghost"
                                                        @click.prevent="rejectApplication(application.id)"
                                                    >
                                                        Move to rejected
                                                    </Button>
                                                </div>
                                            </div>
                                        </component>
                                    </div>
                                    <div v-else class="py-8 text-center">
                                        <Users class="mx-auto h-12 w-12 text-gray-400" />
                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                            No applications yet
                                        </p>
                                    </div>
                                </TabsContent>

                                <TabsContent value="pending" class="mt-4">
                                    <div v-if="filteredApplications.length > 0" class="space-y-3">
                                        <component
                                            :is="application.user_archived ? 'div' : Link"
                                            v-for="application in filteredApplications"
                                            :key="application.id"
                                            :href="!application.user_archived ? hr.applications.show(application.id).url : undefined"
                                            :class="[
                                                'block rounded-lg border p-4 transition-colors dark:border-gray-700',
                                                application.user_archived
                                                    ? 'cursor-not-allowed opacity-60'
                                                    : 'hover:bg-muted/50'
                                            ]"
                                        >
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1 space-y-1">
                                                    <div class="flex items-center gap-2">
                                                        <p class="font-medium text-gray-900 dark:text-gray-100">
                                                            {{ application.user.name }}
                                                        </p>
                                                        <Badge :class="getApplicationStatusColor(application.status)">
                                                            {{ application.status }}
                                                        </Badge>
                                                        <Badge
                                                            v-if="application.user_archived"
                                                            class="bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                        >
                                                            <ArchiveX class="mr-1 h-3 w-3" />
                                                            Archived
                                                        </Badge>
                                                    </div>
                                                    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                                        <Mail class="h-3 w-3" />
                                                        {{ application.user.email }}
                                                    </div>
                                                    <p class="text-xs text-gray-400 dark:text-gray-500">
                                                        Applied {{ formatDate(application.applied_at) }}
                                                    </p>
                                                </div>
                                                <div v-if="application.user_archived && application.status !== 'rejected'">
                                                    <Button
                                                        size="sm"
                                                        variant="ghost"
                                                        @click.prevent="rejectApplication(application.id)"
                                                    >
                                                        Move to rejected
                                                    </Button>
                                                </div>
                                            </div>
                                        </component>
                                    </div>
                                    <div v-else class="py-8 text-center text-gray-500 dark:text-gray-400">
                                        No pending applications
                                    </div>
                                </TabsContent>

                                <TabsContent value="reviewing" class="mt-4">
                                    <div v-if="filteredApplications.length > 0" class="space-y-3">
                                        <component
                                            :is="application.user_archived ? 'div' : Link"
                                            v-for="application in filteredApplications"
                                            :key="application.id"
                                            :href="!application.user_archived ? hr.applications.show(application.id).url : undefined"
                                            :class="[
                                                'block rounded-lg border p-4 transition-colors dark:border-gray-700',
                                                application.user_archived
                                                    ? 'cursor-not-allowed opacity-60'
                                                    : 'hover:bg-muted/50'
                                            ]"
                                        >
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1 space-y-1">
                                                    <div class="flex items-center gap-2">
                                                        <p class="font-medium text-gray-900 dark:text-gray-100">
                                                            {{ application.user.name }}
                                                        </p>
                                                        <Badge :class="getApplicationStatusColor(application.status)">
                                                            {{ application.status }}
                                                        </Badge>
                                                        <Badge
                                                            v-if="application.user_archived"
                                                            class="bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                        >
                                                            <ArchiveX class="mr-1 h-3 w-3" />
                                                            Archived
                                                        </Badge>
                                                    </div>
                                                    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                                        <Mail class="h-3 w-3" />
                                                        {{ application.user.email }}
                                                    </div>
                                                    <p class="text-xs text-gray-400 dark:text-gray-500">
                                                        Applied {{ formatDate(application.applied_at) }}
                                                    </p>
                                                </div>
                                                <div v-if="application.user_archived && application.status !== 'rejected'">
                                                    <Button
                                                        size="sm"
                                                        variant="ghost"
                                                        @click.prevent="rejectApplication(application.id)"
                                                    >
                                                        Move to rejected
                                                    </Button>
                                                </div>
                                            </div>
                                        </component>
                                    </div>
                                    <div v-else class="py-8 text-center text-gray-500 dark:text-gray-400">
                                        No applications under review
                                    </div>
                                </TabsContent>

                                <TabsContent value="accepted" class="mt-4">
                                    <div v-if="filteredApplications.length > 0" class="space-y-3">
                                        <component
                                            :is="application.user_archived ? 'div' : Link"
                                            v-for="application in filteredApplications"
                                            :key="application.id"
                                            :href="!application.user_archived ? hr.applications.show(application.id).url : undefined"
                                            :class="[
                                                'block rounded-lg border p-4 transition-colors dark:border-gray-700',
                                                application.user_archived
                                                    ? 'cursor-not-allowed opacity-60'
                                                    : 'hover:bg-muted/50'
                                            ]"
                                        >
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1 space-y-1">
                                                    <div class="flex items-center gap-2">
                                                        <p class="font-medium text-gray-900 dark:text-gray-100">
                                                            {{ application.user.name }}
                                                        </p>
                                                        <Badge :class="getApplicationStatusColor(application.status)">
                                                            {{ application.status }}
                                                        </Badge>
                                                        <Badge
                                                            v-if="application.user_archived"
                                                            class="bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                        >
                                                            <ArchiveX class="mr-1 h-3 w-3" />
                                                            Archived
                                                        </Badge>
                                                    </div>
                                                    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                                        <Mail class="h-3 w-3" />
                                                        {{ application.user.email }}
                                                    </div>
                                                    <p class="text-xs text-gray-400 dark:text-gray-500">
                                                        Applied {{ formatDate(application.applied_at) }}
                                                    </p>
                                                </div>
                                                <div v-if="application.user_archived && application.status !== 'rejected'">
                                                    <Button
                                                        size="sm"
                                                        variant="ghost"
                                                        @click.prevent="rejectApplication(application.id)"
                                                    >
                                                        Move to rejected
                                                    </Button>
                                                </div>
                                            </div>
                                        </component>
                                    </div>
                                    <div v-else class="py-8 text-center text-gray-500 dark:text-gray-400">
                                        No accepted applications
                                    </div>
                                </TabsContent>

                                <TabsContent value="rejected" class="mt-4">
                                    <div v-if="filteredApplications.length > 0" class="space-y-3">
                                        <component
                                            :is="application.user_archived ? 'div' : Link"
                                            v-for="application in filteredApplications"
                                            :key="application.id"
                                            :href="!application.user_archived ? hr.applications.show(application.id).url : undefined"
                                            :class="[
                                                'block rounded-lg border p-4 transition-colors dark:border-gray-700',
                                                application.user_archived
                                                    ? 'cursor-not-allowed opacity-60'
                                                    : 'hover:bg-muted/50'
                                            ]"
                                        >
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1 space-y-1">
                                                    <div class="flex items-center gap-2">
                                                        <p class="font-medium text-gray-900 dark:text-gray-100">
                                                            {{ application.user.name }}
                                                        </p>
                                                        <Badge :class="getApplicationStatusColor(application.status)">
                                                            {{ application.status }}
                                                        </Badge>
                                                        <Badge
                                                            v-if="application.user_archived"
                                                            class="bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                        >
                                                            <ArchiveX class="mr-1 h-3 w-3" />
                                                            Archived
                                                        </Badge>
                                                    </div>
                                                    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                                        <Mail class="h-3 w-3" />
                                                        {{ application.user.email }}
                                                    </div>
                                                    <p class="text-xs text-gray-400 dark:text-gray-500">
                                                        Applied {{ formatDate(application.applied_at) }}
                                                    </p>
                                                </div>
                                                <div v-if="application.user_archived && application.status !== 'rejected'">
                                                    <Button
                                                        size="sm"
                                                        variant="ghost"
                                                        @click.prevent="rejectApplication(application.id)"
                                                    >
                                                        Move to rejected
                                                    </Button>
                                                </div>
                                            </div>
                                        </component>
                                    </div>
                                    <div v-else class="py-8 text-center text-gray-500 dark:text-gray-400">
                                        No rejected applications
                                    </div>
                                </TabsContent>
                            </Tabs>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Upgrade Tier Card -->
                    <Card v-if="upgradeOptions && Object.keys(upgradeOptions).length > 0">
                        <CardHeader>
                            <div class="flex items-center gap-2">
                                <Zap class="h-5 w-5" />
                                <CardTitle>Upgrade Tier</CardTitle>
                            </div>
                            <CardDescription>
                                Boost your position visibility with a higher tier. Featured and Top tiers are prominently displayed on our homepage.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div
                                v-for="(option, key) in upgradeOptions"
                                :key="key"
                                class="rounded-lg border p-3 space-y-2"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <component
                                            :is="option.tier === 'top' ? Star : Sparkles"
                                            class="h-4 w-4"
                                        />
                                        <span class="font-medium">{{ option.label }}</span>
                                        <Badge v-if="option.tier === 'featured' || option.tier === 'top'" variant="outline" class="text-xs">
                                            ⭐ Homepage
                                        </Badge>
                                    </div>
                                    <Badge variant="secondary">
                                        ${{ option.price.toFixed(2) }}
                                    </Badge>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    <span v-if="option.remaining_days !== null">
                                        Prorated for {{ option.remaining_days }} remaining days
                                    </span>
                                    <span v-else>
                                        Full upgrade price
                                    </span>
                                </p>
                                <Button
                                    size="sm"
                                    class="w-full"
                                    @click="upgradeTier(option.tier)"
                                >
                                    Upgrade to {{ option.label }}
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Statistics -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Statistics</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                    <Users class="h-4 w-4" />
                                    <span>Applications</span>
                                </div>
                                <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    {{ position.applications_count }}
                                </span>
                            </div>
                            <Separator />
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                    <Eye class="h-4 w-4" />
                                    <span>Views</span>
                                </div>
                                <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    {{ position.views_count }}
                                </span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Position Info -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Position Details</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4 text-sm">
                            <div>
                                <p class="text-gray-500 dark:text-gray-400">Created by</p>
                                <p class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ position.creator.name }}
                                </p>
                            </div>
                            <Separator />
                            <div>
                                <p class="text-gray-500 dark:text-gray-400">Created</p>
                                <p class="text-gray-900 dark:text-gray-100">
                                    {{ formatDate(position.created_at) }}
                                </p>
                            </div>
                            <Separator />
                            <div v-if="position.published_at">
                                <p class="text-gray-500 dark:text-gray-400">Published</p>
                                <p class="text-gray-900 dark:text-gray-100">
                                    {{ formatDate(position.published_at) }}
                                </p>
                            </div>
                            <Separator v-if="position.published_at" />
                            <div>
                                <p class="text-gray-500 dark:text-gray-400">Applications</p>
                                <p class="text-gray-900 dark:text-gray-100">
                                    {{
                                        position.allow_platform_applications
                                            ? 'Enabled on platform'
                                            : 'Disabled'
                                    }}
                                </p>
                            </div>
                            <Separator />
                            <div v-if="position.is_external && position.external_url">
                                <p class="mb-1 text-gray-500 dark:text-gray-400">External Link</p>
                                <a
                                    :href="position.external_url"
                                    target="_blank"
                                    class="text-gray-600 hover:underline dark:text-gray-400"
                                >
                                    View external posting →
                                </a>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Analytics -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Analytics</CardTitle>
                            <CardDescription>Last 30 days</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <!-- Views by Device -->
                            <div v-if="analytics.devices.length > 0">
                                <h4 class="mb-3 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    Devices
                                </h4>
                                <div class="space-y-3">
                                    <div
                                        v-for="deviceData in analytics.devices"
                                        :key="`${deviceData.device}-${deviceData.browser}-${deviceData.os}`"
                                        class="flex items-start justify-between"
                                    >
                                        <div class="flex items-start gap-2">
                                            <component
                                                :is="getDeviceIcon(deviceData.device_type)"
                                                class="mt-0.5 h-4 w-4 shrink-0 text-gray-500 dark:text-gray-400"
                                            />
                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    {{ deviceData.device }}
                                                </span>
                                                <span
                                                    v-if="deviceData.browser || deviceData.os"
                                                    class="text-xs text-gray-500 dark:text-gray-400"
                                                >
                                                    <template v-if="deviceData.browser">{{ deviceData.browser }}</template>
                                                    <template v-if="deviceData.browser && deviceData.os"> • </template>
                                                    <template v-if="deviceData.os">{{ deviceData.os }}</template>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ deviceData.count }}
                                            </span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                ({{
                                                    Math.round(
                                                        (deviceData.count / analytics.total_views) * 100
                                                    )
                                                }}%)
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <Separator v-if="analytics.devices.length > 0 && analytics.countries.length > 0" />

                            <!-- Views by Country -->
                            <div v-if="analytics.countries.length > 0">
                                <h4 class="mb-3 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    Top Countries
                                </h4>
                                <div class="space-y-3">
                                    <div
                                        v-for="countryData in analytics.countries"
                                        :key="countryData.country"
                                        class="flex items-center justify-between"
                                    >
                                        <div class="flex items-center gap-2">
                                            <span class="text-lg">{{ getCountryFlag(countryData.country) }}</span>
                                            <span class="text-sm text-gray-700 dark:text-gray-300">
                                                {{ getCountryName(countryData.country) }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ countryData.count }}
                                            </span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                ({{
                                                    Math.round(
                                                        (countryData.count / analytics.total_views) * 100
                                                    )
                                                }}%)
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Kosovo Footnote -->
                                <div
                                    v-if="analytics.countries.some((c) => c.country.toUpperCase() === 'XK')"
                                    class="mt-4 border-t pt-3 text-xs text-gray-500 dark:text-gray-400"
                                >
                                    <p>
                                        * Kosovo's status is
                                        <a
                                            href="https://en.wikipedia.org/wiki/Belgrade–Pristina_Dialogue#cite_note-32"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="text-gray-600 hover:underline dark:text-gray-400"
                                        >
                                            disputed
                                        </a>
                                        . Serbia does not recognize Kosovo as a sovereign state.
                                    </p>
                                </div>
                            </div>

                            <!-- Empty State -->
                            <div
                                v-if="analytics.devices.length === 0 && analytics.countries.length === 0"
                                class="py-8 text-center"
                            >
                                <Eye class="mx-auto h-12 w-12 text-gray-400" />
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    No analytics data yet
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Close Applications Confirmation Dialog -->
        <AlertDialog v-model:open="showCloseConfirmDialog">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Close Applications?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to close applications for this position? 
                        This will prevent new applicants from applying, but existing applications will remain visible.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction 
                        class="bg-destructive hover:bg-destructive/90"
                        @click.prevent="performToggle"
                    >
                        Close Applications
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppLayout>
</template>

