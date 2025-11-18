<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea/index';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import RichTextEditor from '@/components/RichTextEditor.vue';
import TechnologySelector from '@/components/TechnologySelector.vue';
import CustomQuestionBuilder, { type CustomQuestion } from '@/components/CustomQuestionBuilder.vue';
import { toast } from 'vue-sonner';

interface Technology {
    id: number;
    name: string;
    slug: string;
}

interface Company {
    id: number;
    name: string;
}

const props = defineProps<{
    technologies: Technology[];
    companies: Company[];
}>();

const form = useForm({
    title: '',
    short_description: '',
    long_description: '',
    company_id: props.companies.length > 0 ? props.companies[0].id : null,
    seniority: '',
    salary_min: null as number | null,
    salary_max: null as number | null,
    remote_type: 'global',
    location_restriction: '',
    status: 'draft',
    is_featured: false,
    is_external: false,
    external_apply_url: '',
    allow_platform_applications: true,
    expires_at: '',
    technology_ids: [] as number[],
    custom_questions: [] as CustomQuestion[],
});

const submit = () => {
    form.post(route('hr.positions.store'), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Position created successfully!');
        },
        onError: () => {
            toast.error('There was an error creating the position. Please check the form.');
        },
    });
};

const breadcrumbs = [
    {
        title: 'HR Dashboard',
        href: route('hr.dashboard'),
    },
    {
        title: 'Positions',
        href: route('hr.positions.index'),
    },
    {
        title: 'Create',
        href: route('hr.positions.create'),
    },
];
</script>

<template>
    <Head title="Create Position" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full max-w-7xl p-4">
            <div class="mb-6">
                <h1 class="text-2xl font-bold">Create New Position</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Fill in the details below to create a new job position
                </p>
            </div>

            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
                    <!-- Main Content - 3/4 width -->
                    <div class="space-y-6 lg:col-span-3">
                        <!-- Title -->
                        <Card>
                            <CardContent class="pt-6">
                                <div class="space-y-2">
                                    <Label for="title">Position Title *</Label>
                                    <Input
                                        id="title"
                                        v-model="form.title"
                                        placeholder="e.g., Senior Laravel Developer"
                                        :class="{ 'border-red-500': form.errors.title }"
                                    />
                                    <p v-if="form.errors.title" class="text-sm text-red-500">
                                        {{ form.errors.title }}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Short Description -->
                        <Card>
                            <CardContent class="pt-6">
                                <div class="space-y-2">
                                    <Label for="short_description">Short Description *</Label>
                                    <Textarea
                                        id="short_description"
                                        v-model="form.short_description"
                                        placeholder="A brief overview that will appear in job listings (max 200 characters)"
                                        rows="3"
                                        maxlength="200"
                                        :class="{ 'border-red-500': form.errors.short_description }"
                                    />
                                    <div class="flex justify-between">
                                        <p
                                            v-if="form.errors.short_description"
                                            class="text-sm text-red-500"
                                        >
                                            {{ form.errors.short_description }}
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ form.short_description.length }} / 200
                                        </p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Long Description -->
                        <Card>
                            <CardContent class="pt-6">
                                <div class="space-y-2">
                                    <Label for="long_description">Full Job Description *</Label>
                                    <RichTextEditor
                                        v-model="form.long_description"
                                        placeholder="Provide a detailed description of the role, responsibilities, requirements, and benefits..."
                                    />
                                    <p v-if="form.errors.long_description" class="text-sm text-red-500">
                                        {{ form.errors.long_description }}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Application Settings Section -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Application Settings</CardTitle>
                                <CardDescription>
                                    Configure how candidates will apply for this position
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
                                    <!-- Application Method - 1/4 width -->
                                    <div class="lg:col-span-1">
                                        <Label class="text-sm font-medium">Application Method</Label>
                                        <div class="mt-3 space-y-3">
                                            <div class="flex items-center space-x-2">
                                                <input
                                                    id="platform_application"
                                                    type="radio"
                                                    :checked="!form.is_external"
                                                    @change="form.is_external = false; form.external_apply_url = ''; form.allow_platform_applications = true"
                                                    class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600 dark:border-gray-600 dark:bg-gray-800"
                                                />
                                                <Label for="platform_application" class="cursor-pointer font-normal">
                                                    Platform applications
                                                </Label>
                                            </div>

                                            <div class="flex items-center space-x-2">
                                                <input
                                                    id="external_application"
                                                    type="radio"
                                                    :checked="form.is_external"
                                                    @change="form.is_external = true; form.allow_platform_applications = false"
                                                    class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600 dark:border-gray-600 dark:bg-gray-800"
                                                />
                                                <Label for="external_application" class="cursor-pointer font-normal">
                                                    External application
                                                </Label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Application Content - 3/4 width -->
                                    <div class="lg:col-span-3">
                                        <!-- Custom Questions -->
                                        <div v-if="!form.is_external" class="space-y-4">
                                            <div>
                                                <Label class="text-sm font-medium">Custom Application Questions</Label>
                                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                    Add specific questions that candidates must answer when applying for this position.
                                                </p>
                                            </div>
                                            <CustomQuestionBuilder v-model="form.custom_questions" />
                                        </div>

                                        <!-- External Application URL -->
                                        <div v-if="form.is_external" class="space-y-4">
                                            <div>
                                                <Label class="text-sm font-medium" for="external_apply_url_main">External Application URL*</Label>
                                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                    Provide the URL or email address where candidates should apply.
                                                </p>

                                                <Input
                                                    id="external_apply_url_main"
                                                    v-model="form.external_apply_url"
                                                    placeholder="https://company.com/apply or apply@company.com"
                                                />
                                            </div>
                                            <div class="space-y-2">
                                                <p
                                                    v-if="form.errors.external_apply_url"
                                                    class="text-sm text-red-500"
                                                >
                                                    {{ form.errors.external_apply_url }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Sidebar - 1/4 width -->
                    <div class="space-y-6 lg:col-span-1">
                        <!-- Status Section -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="text-base">Status</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="space-y-2">
                                    <Label for="status">Publication Status *</Label>
                                    <Select v-model="form.status">
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="draft">Draft</SelectItem>
                                            <SelectItem value="published">Published</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="form.errors.status" class="text-sm text-red-500">
                                        {{ form.errors.status }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="expires_at">Expiration Date *</Label>
                                    <Input
                                        id="expires_at"
                                        v-model="form.expires_at"
                                        type="date"
                                    />
                                    <p v-if="form.errors.expires_at" class="text-sm text-red-500">
                                        {{ form.errors.expires_at }}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Location Section -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="text-base">Location</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="space-y-2">
                                    <Label for="remote_type">Remote Type *</Label>
                                    <Select v-model="form.remote_type">
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="global">Global</SelectItem>
                                            <SelectItem value="timezone">Timezone</SelectItem>
                                            <SelectItem value="country">Country</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="form.errors.remote_type" class="text-sm text-red-500">
                                        {{ form.errors.remote_type }}
                                    </p>
                                </div>

                                <div v-if="form.remote_type !== 'global'" class="space-y-2">
                                    <Label for="location_restriction">
                                        {{ form.remote_type === 'timezone' ? 'Timezone Restriction' : 'Country Restriction' }}
                                    </Label>
                                    <Select v-if="form.remote_type === 'country'" v-model="form.location_restriction">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select a country or region" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="EU">European Union (EU)</SelectItem>
                                            <SelectItem value="EMEA">Europe, Middle East & Africa (EMEA)</SelectItem>
                                            <SelectItem value="Afghanistan">Afghanistan</SelectItem>
                                            <SelectItem value="Albania">Albania</SelectItem>
                                            <SelectItem value="Algeria">Algeria</SelectItem>
                                            <SelectItem value="Andorra">Andorra</SelectItem>
                                            <SelectItem value="Angola">Angola</SelectItem>
                                            <SelectItem value="Argentina">Argentina</SelectItem>
                                            <SelectItem value="Armenia">Armenia</SelectItem>
                                            <SelectItem value="Australia">Australia</SelectItem>
                                            <SelectItem value="Austria">Austria</SelectItem>
                                            <SelectItem value="Azerbaijan">Azerbaijan</SelectItem>
                                            <SelectItem value="Bahamas">Bahamas</SelectItem>
                                            <SelectItem value="Bahrain">Bahrain</SelectItem>
                                            <SelectItem value="Bangladesh">Bangladesh</SelectItem>
                                            <SelectItem value="Barbados">Barbados</SelectItem>
                                            <SelectItem value="Belarus">Belarus</SelectItem>
                                            <SelectItem value="Belgium">Belgium</SelectItem>
                                            <SelectItem value="Belize">Belize</SelectItem>
                                            <SelectItem value="Benin">Benin</SelectItem>
                                            <SelectItem value="Bhutan">Bhutan</SelectItem>
                                            <SelectItem value="Bolivia">Bolivia</SelectItem>
                                            <SelectItem value="Bosnia and Herzegovina">Bosnia and Herzegovina</SelectItem>
                                            <SelectItem value="Botswana">Botswana</SelectItem>
                                            <SelectItem value="Brazil">Brazil</SelectItem>
                                            <SelectItem value="Brunei">Brunei</SelectItem>
                                            <SelectItem value="Bulgaria">Bulgaria</SelectItem>
                                            <SelectItem value="Burkina Faso">Burkina Faso</SelectItem>
                                            <SelectItem value="Burundi">Burundi</SelectItem>
                                            <SelectItem value="Cambodia">Cambodia</SelectItem>
                                            <SelectItem value="Cameroon">Cameroon</SelectItem>
                                            <SelectItem value="Canada">Canada</SelectItem>
                                            <SelectItem value="Cape Verde">Cape Verde</SelectItem>
                                            <SelectItem value="Central African Republic">Central African Republic</SelectItem>
                                            <SelectItem value="Chad">Chad</SelectItem>
                                            <SelectItem value="Chile">Chile</SelectItem>
                                            <SelectItem value="China">China</SelectItem>
                                            <SelectItem value="Colombia">Colombia</SelectItem>
                                            <SelectItem value="Comoros">Comoros</SelectItem>
                                            <SelectItem value="Congo">Congo</SelectItem>
                                            <SelectItem value="Costa Rica">Costa Rica</SelectItem>
                                            <SelectItem value="Croatia">Croatia</SelectItem>
                                            <SelectItem value="Cuba">Cuba</SelectItem>
                                            <SelectItem value="Cyprus">Cyprus</SelectItem>
                                            <SelectItem value="Czech Republic">Czech Republic</SelectItem>
                                            <SelectItem value="Denmark">Denmark</SelectItem>
                                            <SelectItem value="Djibouti">Djibouti</SelectItem>
                                            <SelectItem value="Dominica">Dominica</SelectItem>
                                            <SelectItem value="Dominican Republic">Dominican Republic</SelectItem>
                                            <SelectItem value="Ecuador">Ecuador</SelectItem>
                                            <SelectItem value="Egypt">Egypt</SelectItem>
                                            <SelectItem value="El Salvador">El Salvador</SelectItem>
                                            <SelectItem value="Equatorial Guinea">Equatorial Guinea</SelectItem>
                                            <SelectItem value="Eritrea">Eritrea</SelectItem>
                                            <SelectItem value="Estonia">Estonia</SelectItem>
                                            <SelectItem value="Eswatini">Eswatini</SelectItem>
                                            <SelectItem value="Ethiopia">Ethiopia</SelectItem>
                                            <SelectItem value="Fiji">Fiji</SelectItem>
                                            <SelectItem value="Finland">Finland</SelectItem>
                                            <SelectItem value="France">France</SelectItem>
                                            <SelectItem value="Gabon">Gabon</SelectItem>
                                            <SelectItem value="Gambia">Gambia</SelectItem>
                                            <SelectItem value="Georgia">Georgia</SelectItem>
                                            <SelectItem value="Germany">Germany</SelectItem>
                                            <SelectItem value="Ghana">Ghana</SelectItem>
                                            <SelectItem value="Greece">Greece</SelectItem>
                                            <SelectItem value="Grenada">Grenada</SelectItem>
                                            <SelectItem value="Guatemala">Guatemala</SelectItem>
                                            <SelectItem value="Guinea">Guinea</SelectItem>
                                            <SelectItem value="Guinea-Bissau">Guinea-Bissau</SelectItem>
                                            <SelectItem value="Guyana">Guyana</SelectItem>
                                            <SelectItem value="Haiti">Haiti</SelectItem>
                                            <SelectItem value="Honduras">Honduras</SelectItem>
                                            <SelectItem value="Hungary">Hungary</SelectItem>
                                            <SelectItem value="Iceland">Iceland</SelectItem>
                                            <SelectItem value="India">India</SelectItem>
                                            <SelectItem value="Indonesia">Indonesia</SelectItem>
                                            <SelectItem value="Iran">Iran</SelectItem>
                                            <SelectItem value="Iraq">Iraq</SelectItem>
                                            <SelectItem value="Ireland">Ireland</SelectItem>
                                            <SelectItem value="Israel">Israel</SelectItem>
                                            <SelectItem value="Italy">Italy</SelectItem>
                                            <SelectItem value="Jamaica">Jamaica</SelectItem>
                                            <SelectItem value="Japan">Japan</SelectItem>
                                            <SelectItem value="Jordan">Jordan</SelectItem>
                                            <SelectItem value="Kazakhstan">Kazakhstan</SelectItem>
                                            <SelectItem value="Kenya">Kenya</SelectItem>
                                            <SelectItem value="Kiribati">Kiribati</SelectItem>
                                            <SelectItem value="Kosovo">Kosovo*</SelectItem>
                                            <SelectItem value="Kuwait">Kuwait</SelectItem>
                                            <SelectItem value="Kyrgyzstan">Kyrgyzstan</SelectItem>
                                            <SelectItem value="Laos">Laos</SelectItem>
                                            <SelectItem value="Latvia">Latvia</SelectItem>
                                            <SelectItem value="Lebanon">Lebanon</SelectItem>
                                            <SelectItem value="Lesotho">Lesotho</SelectItem>
                                            <SelectItem value="Liberia">Liberia</SelectItem>
                                            <SelectItem value="Libya">Libya</SelectItem>
                                            <SelectItem value="Liechtenstein">Liechtenstein</SelectItem>
                                            <SelectItem value="Lithuania">Lithuania</SelectItem>
                                            <SelectItem value="Luxembourg">Luxembourg</SelectItem>
                                            <SelectItem value="Madagascar">Madagascar</SelectItem>
                                            <SelectItem value="Malawi">Malawi</SelectItem>
                                            <SelectItem value="Malaysia">Malaysia</SelectItem>
                                            <SelectItem value="Maldives">Maldives</SelectItem>
                                            <SelectItem value="Mali">Mali</SelectItem>
                                            <SelectItem value="Malta">Malta</SelectItem>
                                            <SelectItem value="Marshall Islands">Marshall Islands</SelectItem>
                                            <SelectItem value="Mauritania">Mauritania</SelectItem>
                                            <SelectItem value="Mauritius">Mauritius</SelectItem>
                                            <SelectItem value="Mexico">Mexico</SelectItem>
                                            <SelectItem value="Micronesia">Micronesia</SelectItem>
                                            <SelectItem value="Moldova">Moldova</SelectItem>
                                            <SelectItem value="Monaco">Monaco</SelectItem>
                                            <SelectItem value="Mongolia">Mongolia</SelectItem>
                                            <SelectItem value="Montenegro">Montenegro</SelectItem>
                                            <SelectItem value="Morocco">Morocco</SelectItem>
                                            <SelectItem value="Mozambique">Mozambique</SelectItem>
                                            <SelectItem value="Myanmar">Myanmar</SelectItem>
                                            <SelectItem value="Namibia">Namibia</SelectItem>
                                            <SelectItem value="Nauru">Nauru</SelectItem>
                                            <SelectItem value="Nepal">Nepal</SelectItem>
                                            <SelectItem value="Netherlands">Netherlands</SelectItem>
                                            <SelectItem value="New Zealand">New Zealand</SelectItem>
                                            <SelectItem value="Nicaragua">Nicaragua</SelectItem>
                                            <SelectItem value="Niger">Niger</SelectItem>
                                            <SelectItem value="Nigeria">Nigeria</SelectItem>
                                            <SelectItem value="North Korea">North Korea</SelectItem>
                                            <SelectItem value="North Macedonia">North Macedonia</SelectItem>
                                            <SelectItem value="Norway">Norway</SelectItem>
                                            <SelectItem value="Oman">Oman</SelectItem>
                                            <SelectItem value="Pakistan">Pakistan</SelectItem>
                                            <SelectItem value="Palau">Palau</SelectItem>
                                            <SelectItem value="Palestine">Palestine</SelectItem>
                                            <SelectItem value="Panama">Panama</SelectItem>
                                            <SelectItem value="Papua New Guinea">Papua New Guinea</SelectItem>
                                            <SelectItem value="Paraguay">Paraguay</SelectItem>
                                            <SelectItem value="Peru">Peru</SelectItem>
                                            <SelectItem value="Philippines">Philippines</SelectItem>
                                            <SelectItem value="Poland">Poland</SelectItem>
                                            <SelectItem value="Portugal">Portugal</SelectItem>
                                            <SelectItem value="Qatar">Qatar</SelectItem>
                                            <SelectItem value="Romania">Romania</SelectItem>
                                            <SelectItem value="Russia">Russia</SelectItem>
                                            <SelectItem value="Rwanda">Rwanda</SelectItem>
                                            <SelectItem value="Saint Kitts and Nevis">Saint Kitts and Nevis</SelectItem>
                                            <SelectItem value="Saint Lucia">Saint Lucia</SelectItem>
                                            <SelectItem value="Saint Vincent and the Grenadines">Saint Vincent and the Grenadines</SelectItem>
                                            <SelectItem value="Samoa">Samoa</SelectItem>
                                            <SelectItem value="San Marino">San Marino</SelectItem>
                                            <SelectItem value="Sao Tome and Principe">Sao Tome and Principe</SelectItem>
                                            <SelectItem value="Saudi Arabia">Saudi Arabia</SelectItem>
                                            <SelectItem value="Senegal">Senegal</SelectItem>
                                            <SelectItem value="Serbia">Serbia</SelectItem>
                                            <SelectItem value="Seychelles">Seychelles</SelectItem>
                                            <SelectItem value="Sierra Leone">Sierra Leone</SelectItem>
                                            <SelectItem value="Singapore">Singapore</SelectItem>
                                            <SelectItem value="Slovakia">Slovakia</SelectItem>
                                            <SelectItem value="Slovenia">Slovenia</SelectItem>
                                            <SelectItem value="Solomon Islands">Solomon Islands</SelectItem>
                                            <SelectItem value="Somalia">Somalia</SelectItem>
                                            <SelectItem value="South Africa">South Africa</SelectItem>
                                            <SelectItem value="South Korea">South Korea</SelectItem>
                                            <SelectItem value="South Sudan">South Sudan</SelectItem>
                                            <SelectItem value="Spain">Spain</SelectItem>
                                            <SelectItem value="Sri Lanka">Sri Lanka</SelectItem>
                                            <SelectItem value="Sudan">Sudan</SelectItem>
                                            <SelectItem value="Suriname">Suriname</SelectItem>
                                            <SelectItem value="Sweden">Sweden</SelectItem>
                                            <SelectItem value="Switzerland">Switzerland</SelectItem>
                                            <SelectItem value="Syria">Syria</SelectItem>
                                            <SelectItem value="Taiwan">Taiwan</SelectItem>
                                            <SelectItem value="Tajikistan">Tajikistan</SelectItem>
                                            <SelectItem value="Tanzania">Tanzania</SelectItem>
                                            <SelectItem value="Thailand">Thailand</SelectItem>
                                            <SelectItem value="Timor-Leste">Timor-Leste</SelectItem>
                                            <SelectItem value="Togo">Togo</SelectItem>
                                            <SelectItem value="Tonga">Tonga</SelectItem>
                                            <SelectItem value="Trinidad and Tobago">Trinidad and Tobago</SelectItem>
                                            <SelectItem value="Tunisia">Tunisia</SelectItem>
                                            <SelectItem value="Turkey">Turkey</SelectItem>
                                            <SelectItem value="Turkmenistan">Turkmenistan</SelectItem>
                                            <SelectItem value="Tuvalu">Tuvalu</SelectItem>
                                            <SelectItem value="Uganda">Uganda</SelectItem>
                                            <SelectItem value="Ukraine">Ukraine</SelectItem>
                                            <SelectItem value="United Arab Emirates">United Arab Emirates</SelectItem>
                                            <SelectItem value="United Kingdom">United Kingdom</SelectItem>
                                            <SelectItem value="United States">United States</SelectItem>
                                            <SelectItem value="Uruguay">Uruguay</SelectItem>
                                            <SelectItem value="Uzbekistan">Uzbekistan</SelectItem>
                                            <SelectItem value="Vanuatu">Vanuatu</SelectItem>
                                            <SelectItem value="Vatican City">Vatican City</SelectItem>
                                            <SelectItem value="Venezuela">Venezuela</SelectItem>
                                            <SelectItem value="Vietnam">Vietnam</SelectItem>
                                            <SelectItem value="Yemen">Yemen</SelectItem>
                                            <SelectItem value="Zambia">Zambia</SelectItem>
                                            <SelectItem value="Zimbabwe">Zimbabwe</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <Input
                                        v-else
                                        id="location_restriction"
                                        v-model="form.location_restriction"
                                        placeholder="e.g., UTC-5 to UTC+2"
                                    />
                                    <p
                                        v-if="form.errors.location_restriction"
                                        class="text-sm text-red-500"
                                    >
                                        {{ form.errors.location_restriction }}
                                    </p>

                                    <!-- Kosovo Disclaimer -->
                                    <div
                                        v-if="form.remote_type === 'country' && form.location_restriction === 'Kosovo'"
                                        class="mt-2 text-xs text-gray-500 dark:text-gray-400"
                                    >
                                        <p>
                                            * Kosovo's status is
                                            <a
                                                href="https://en.wikipedia.org/wiki/Belgrade–Pristina_Dialogue#cite_note-32"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="text-blue-600 hover:underline dark:text-blue-400"
                                            >
                                                disputed
                                            </a>
                                            . Serbia does not recognize Kosovo as a sovereign state.
                                        </p>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Basic Info Section -->
                        <Card v-if="companies.length > 1">
                            <CardHeader>
                                <CardTitle class="text-base">Basic Info</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="space-y-2">
                                    <Label for="company_id">Company *</Label>
                                    <Select v-model="form.company_id">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select a company" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="company in companies"
                                                :key="company.id"
                                                :value="company.id.toString()"
                                            >
                                                {{ company.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="form.errors.company_id" class="text-sm text-red-500">
                                        {{ form.errors.company_id }}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Requirements Section -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="text-base">Requirements</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="space-y-2">
                                    <Label>Technologies *</Label>
                                    <TechnologySelector
                                        v-model="form.technology_ids"
                                        :technologies="technologies"
                                    />
                                    <p v-if="form.errors.technology_ids" class="text-sm text-red-500">
                                        {{ form.errors.technology_ids }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="seniority">Seniority Level</Label>
                                    <Select v-model="form.seniority">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select seniority level" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="junior">Junior</SelectItem>
                                            <SelectItem value="mid">Mid-Level</SelectItem>
                                            <SelectItem value="senior">Senior</SelectItem>
                                            <SelectItem value="lead">Lead</SelectItem>
                                            <SelectItem value="principal">Principal</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="form.errors.seniority" class="text-sm text-red-500">
                                        {{ form.errors.seniority }}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Compensation Section -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="text-base">Compensation</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="space-y-2">
                                    <Label for="salary_min">Min Salary (USD)</Label>
                                    <Input
                                        id="salary_min"
                                        v-model.number="form.salary_min"
                                        type="number"
                                        placeholder="80000"
                                        min="0"
                                        step="1000"
                                    />
                                    <p v-if="form.errors.salary_min" class="text-sm text-red-500">
                                        {{ form.errors.salary_min }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="salary_max">Max Salary (USD)</Label>
                                    <Input
                                        id="salary_max"
                                        v-model.number="form.salary_max"
                                        type="number"
                                        placeholder="120000"
                                        min="0"
                                        step="1000"
                                    />
                                    <p v-if="form.errors.salary_max" class="text-sm text-red-500">
                                        {{ form.errors.salary_max }}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-6 flex justify-end">
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Creating...' : 'Create Position' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

