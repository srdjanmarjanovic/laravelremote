@extends('layouts.public')

@php
use App\Enums\ListingType;
@endphp

@section('title', 'Browse Remote Laravel Jobs')
@section('description', 'Find the best remote Laravel developer positions from top companies. Filter by technology, seniority, and location.')

@section('content')
<div class="bg-gradient-to-br from-accent/20 via-background to-secondary/20 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Hero Section -->
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-bold text-foreground mb-4">
                Find Your Next <span class="text-primary">Remote Laravel Job</span>
            </h1>
            <p class="text-xl text-muted-foreground max-w-3xl mx-auto">
                Join the best remote Laravel teams. Browse {{ $positions->total() }} open positions.
            </p>
        </div>

        <!-- Search and Filters -->
        <div class="bg-card rounded-lg shadow-lg p-6 mb-8 border border-border transition-colors duration-300">
            <form action="/positions" method="GET" class="space-y-4">
                <!-- Search Bar -->
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search by title, description, or company..."
                            class="w-full px-4 py-3 border border-input bg-background text-foreground rounded-lg focus:ring-2 focus:ring-ring focus:border-transparent transition-colors"
                        >
                    </div>
                    <button type="submit" class="px-6 py-3 bg-primary hover:bg-primary/90 text-primary-foreground font-medium rounded-lg transition-colors">
                        Search
                    </button>
                </div>

                <!-- Advanced Filters -->
                <div
                    class="grid grid-cols-1 gap-4 pt-4 border-t border-border"
                    :class="{
                        'sm:grid-cols-2': remoteType === 'country' || remoteType === 'timezone',
                        'sm:grid-cols-3': remoteType !== 'country' && remoteType !== 'timezone'
                    }"
                    x-data="{ remoteType: '{{ request('remote_type') }}' }"
                >
                    <!-- Technology Filter -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">Technology</label>
                        <select name="technology" @change="$el.form.submit()" class="w-full px-4 py-2 border border-input bg-background text-foreground rounded-lg focus:ring-2 focus:ring-ring transition-colors">
                            <option value="">All Technologies</option>
                            @foreach($technologies as $tech)
                                <option value="{{ $tech->slug }}" {{ request('technology') === $tech->slug ? 'selected' : '' }}>
                                    {{ $tech->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Seniority Filter -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">Seniority</label>
                        <select name="seniority" @change="$el.form.submit()" class="w-full px-4 py-2 border border-input bg-background text-foreground rounded-lg focus:ring-2 focus:ring-ring transition-colors">
                            <option value="">All Levels</option>
                            <option value="junior" {{ request('seniority') === 'junior' ? 'selected' : '' }}>Junior</option>
                            <option value="mid" {{ request('seniority') === 'mid' ? 'selected' : '' }}>Mid-Level</option>
                            <option value="senior" {{ request('seniority') === 'senior' ? 'selected' : '' }}>Senior</option>
                            <option value="lead" {{ request('seniority') === 'lead' ? 'selected' : '' }}>Lead</option>
                            <option value="principal" {{ request('seniority') === 'principal' ? 'selected' : '' }}>Principal</option>
                        </select>
                    </div>

                    <!-- Remote Type Filter -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">Remote Type</label>
                        <select name="remote_type" x-model="remoteType" @change="if ($event.target.value !== 'country' && $event.target.value !== 'timezone') $el.form.submit()" class="w-full px-4 py-2 border border-input bg-background text-foreground rounded-lg focus:ring-2 focus:ring-ring transition-colors">
                            <option value="">All Types</option>
                            <option value="global" {{ request('remote_type') === 'global' ? 'selected' : '' }}>Global</option>
                            <option value="timezone" {{ request('remote_type') === 'timezone' ? 'selected' : '' }}>Specific Timezone</option>
                            <option value="country" {{ request('remote_type') === 'country' ? 'selected' : '' }}>Specific Country</option>
                        </select>
                    </div>

                    <!-- Timezone Range Filter (shown when remote_type is timezone) -->
                    <div x-show="remoteType === 'timezone'" x-cloak>
                        <label class="block text-sm font-medium text-foreground mb-2">Timezone Range</label>
                        <div class="flex gap-2">
                            <select name="timezone_from" @change="$el.form.submit()" class="flex-1 px-3 py-2 border border-input bg-background text-foreground rounded-lg focus:ring-2 focus:ring-ring transition-colors text-sm">
                                <option value="">From</option>
                                <option value="-12" {{ request('timezone_from') === '-12' ? 'selected' : '' }}>UTC-12</option>
                                <option value="-11" {{ request('timezone_from') === '-11' ? 'selected' : '' }}>UTC-11</option>
                                <option value="-10" {{ request('timezone_from') === '-10' ? 'selected' : '' }}>UTC-10</option>
                                <option value="-9" {{ request('timezone_from') === '-9' ? 'selected' : '' }}>UTC-9</option>
                                <option value="-8" {{ request('timezone_from') === '-8' ? 'selected' : '' }}>UTC-8</option>
                                <option value="-7" {{ request('timezone_from') === '-7' ? 'selected' : '' }}>UTC-7</option>
                                <option value="-6" {{ request('timezone_from') === '-6' ? 'selected' : '' }}>UTC-6</option>
                                <option value="-5" {{ request('timezone_from') === '-5' ? 'selected' : '' }}>UTC-5</option>
                                <option value="-4" {{ request('timezone_from') === '-4' ? 'selected' : '' }}>UTC-4</option>
                                <option value="-3" {{ request('timezone_from') === '-3' ? 'selected' : '' }}>UTC-3</option>
                                <option value="-2" {{ request('timezone_from') === '-2' ? 'selected' : '' }}>UTC-2</option>
                                <option value="-1" {{ request('timezone_from') === '-1' ? 'selected' : '' }}>UTC-1</option>
                                <option value="0" {{ request('timezone_from') === '0' ? 'selected' : '' }}>UTC+0</option>
                                <option value="1" {{ request('timezone_from') === '1' ? 'selected' : '' }}>UTC+1</option>
                                <option value="2" {{ request('timezone_from') === '2' ? 'selected' : '' }}>UTC+2</option>
                                <option value="3" {{ request('timezone_from') === '3' ? 'selected' : '' }}>UTC+3</option>
                                <option value="4" {{ request('timezone_from') === '4' ? 'selected' : '' }}>UTC+4</option>
                                <option value="5" {{ request('timezone_from') === '5' ? 'selected' : '' }}>UTC+5</option>
                                <option value="6" {{ request('timezone_from') === '6' ? 'selected' : '' }}>UTC+6</option>
                                <option value="7" {{ request('timezone_from') === '7' ? 'selected' : '' }}>UTC+7</option>
                                <option value="8" {{ request('timezone_from') === '8' ? 'selected' : '' }}>UTC+8</option>
                                <option value="9" {{ request('timezone_from') === '9' ? 'selected' : '' }}>UTC+9</option>
                                <option value="10" {{ request('timezone_from') === '10' ? 'selected' : '' }}>UTC+10</option>
                                <option value="11" {{ request('timezone_from') === '11' ? 'selected' : '' }}>UTC+11</option>
                                <option value="12" {{ request('timezone_from') === '12' ? 'selected' : '' }}>UTC+12</option>
                                <option value="13" {{ request('timezone_from') === '13' ? 'selected' : '' }}>UTC+13</option>
                                <option value="14" {{ request('timezone_from') === '14' ? 'selected' : '' }}>UTC+14</option>
                            </select>
                            <select name="timezone_to" @change="$el.form.submit()" class="flex-1 px-3 py-2 border border-input bg-background text-foreground rounded-lg focus:ring-2 focus:ring-ring transition-colors text-sm">
                                <option value="">To</option>
                                <option value="-12" {{ request('timezone_to') === '-12' ? 'selected' : '' }}>UTC-12</option>
                                <option value="-11" {{ request('timezone_to') === '-11' ? 'selected' : '' }}>UTC-11</option>
                                <option value="-10" {{ request('timezone_to') === '-10' ? 'selected' : '' }}>UTC-10</option>
                                <option value="-9" {{ request('timezone_to') === '-9' ? 'selected' : '' }}>UTC-9</option>
                                <option value="-8" {{ request('timezone_to') === '-8' ? 'selected' : '' }}>UTC-8</option>
                                <option value="-7" {{ request('timezone_to') === '-7' ? 'selected' : '' }}>UTC-7</option>
                                <option value="-6" {{ request('timezone_to') === '-6' ? 'selected' : '' }}>UTC-6</option>
                                <option value="-5" {{ request('timezone_to') === '-5' ? 'selected' : '' }}>UTC-5</option>
                                <option value="-4" {{ request('timezone_to') === '-4' ? 'selected' : '' }}>UTC-4</option>
                                <option value="-3" {{ request('timezone_to') === '-3' ? 'selected' : '' }}>UTC-3</option>
                                <option value="-2" {{ request('timezone_to') === '-2' ? 'selected' : '' }}>UTC-2</option>
                                <option value="-1" {{ request('timezone_to') === '-1' ? 'selected' : '' }}>UTC-1</option>
                                <option value="0" {{ request('timezone_to') === '0' ? 'selected' : '' }}>UTC+0</option>
                                <option value="1" {{ request('timezone_to') === '1' ? 'selected' : '' }}>UTC+1</option>
                                <option value="2" {{ request('timezone_to') === '2' ? 'selected' : '' }}>UTC+2</option>
                                <option value="3" {{ request('timezone_to') === '3' ? 'selected' : '' }}>UTC+3</option>
                                <option value="4" {{ request('timezone_to') === '4' ? 'selected' : '' }}>UTC+4</option>
                                <option value="5" {{ request('timezone_to') === '5' ? 'selected' : '' }}>UTC+5</option>
                                <option value="6" {{ request('timezone_to') === '6' ? 'selected' : '' }}>UTC+6</option>
                                <option value="7" {{ request('timezone_to') === '7' ? 'selected' : '' }}>UTC+7</option>
                                <option value="8" {{ request('timezone_to') === '8' ? 'selected' : '' }}>UTC+8</option>
                                <option value="9" {{ request('timezone_to') === '9' ? 'selected' : '' }}>UTC+9</option>
                                <option value="10" {{ request('timezone_to') === '10' ? 'selected' : '' }}>UTC+10</option>
                                <option value="11" {{ request('timezone_to') === '11' ? 'selected' : '' }}>UTC+11</option>
                                <option value="12" {{ request('timezone_to') === '12' ? 'selected' : '' }}>UTC+12</option>
                                <option value="13" {{ request('timezone_to') === '13' ? 'selected' : '' }}>UTC+13</option>
                                <option value="14" {{ request('timezone_to') === '14' ? 'selected' : '' }}>UTC+14</option>
                            </select>
                        </div>
                    </div>

                    <!-- Country/Region Filter (shown when remote_type is country) -->
                    <div x-show="remoteType === 'country'" x-cloak>
                        <label class="block text-sm font-medium text-foreground mb-2">Country / Region</label>
                        <select name="location_restriction" @change="$el.form.submit()" class="w-full px-4 py-2 border border-input bg-background text-foreground rounded-lg focus:ring-2 focus:ring-ring transition-colors">
                            <option value="">Select a country or region</option>
                            <option value="EU" {{ request('location_restriction') === 'EU' ? 'selected' : '' }}>European Union (EU)</option>
                            <option value="EMEA" {{ request('location_restriction') === 'EMEA' ? 'selected' : '' }}>Europe, Middle East & Africa (EMEA)</option>
                            <option value="United States" {{ request('location_restriction') === 'United States' ? 'selected' : '' }}>United States</option>
                            <option value="Afghanistan" {{ request('location_restriction') === 'Afghanistan' ? 'selected' : '' }}>Afghanistan</option>
                            <option value="Albania" {{ request('location_restriction') === 'Albania' ? 'selected' : '' }}>Albania</option>
                            <option value="Algeria" {{ request('location_restriction') === 'Algeria' ? 'selected' : '' }}>Algeria</option>
                            <option value="Andorra" {{ request('location_restriction') === 'Andorra' ? 'selected' : '' }}>Andorra</option>
                            <option value="Angola" {{ request('location_restriction') === 'Angola' ? 'selected' : '' }}>Angola</option>
                            <option value="Argentina" {{ request('location_restriction') === 'Argentina' ? 'selected' : '' }}>Argentina</option>
                            <option value="Armenia" {{ request('location_restriction') === 'Armenia' ? 'selected' : '' }}>Armenia</option>
                            <option value="Australia" {{ request('location_restriction') === 'Australia' ? 'selected' : '' }}>Australia</option>
                            <option value="Austria" {{ request('location_restriction') === 'Austria' ? 'selected' : '' }}>Austria</option>
                            <option value="Azerbaijan" {{ request('location_restriction') === 'Azerbaijan' ? 'selected' : '' }}>Azerbaijan</option>
                            <option value="Bahamas" {{ request('location_restriction') === 'Bahamas' ? 'selected' : '' }}>Bahamas</option>
                            <option value="Bahrain" {{ request('location_restriction') === 'Bahrain' ? 'selected' : '' }}>Bahrain</option>
                            <option value="Bangladesh" {{ request('location_restriction') === 'Bangladesh' ? 'selected' : '' }}>Bangladesh</option>
                            <option value="Barbados" {{ request('location_restriction') === 'Barbados' ? 'selected' : '' }}>Barbados</option>
                            <option value="Belarus" {{ request('location_restriction') === 'Belarus' ? 'selected' : '' }}>Belarus</option>
                            <option value="Belgium" {{ request('location_restriction') === 'Belgium' ? 'selected' : '' }}>Belgium</option>
                            <option value="Belize" {{ request('location_restriction') === 'Belize' ? 'selected' : '' }}>Belize</option>
                            <option value="Benin" {{ request('location_restriction') === 'Benin' ? 'selected' : '' }}>Benin</option>
                            <option value="Bhutan" {{ request('location_restriction') === 'Bhutan' ? 'selected' : '' }}>Bhutan</option>
                            <option value="Bolivia" {{ request('location_restriction') === 'Bolivia' ? 'selected' : '' }}>Bolivia</option>
                            <option value="Bosnia and Herzegovina" {{ request('location_restriction') === 'Bosnia and Herzegovina' ? 'selected' : '' }}>Bosnia and Herzegovina</option>
                            <option value="Botswana" {{ request('location_restriction') === 'Botswana' ? 'selected' : '' }}>Botswana</option>
                            <option value="Brazil" {{ request('location_restriction') === 'Brazil' ? 'selected' : '' }}>Brazil</option>
                            <option value="Brunei" {{ request('location_restriction') === 'Brunei' ? 'selected' : '' }}>Brunei</option>
                            <option value="Bulgaria" {{ request('location_restriction') === 'Bulgaria' ? 'selected' : '' }}>Bulgaria</option>
                            <option value="Burkina Faso" {{ request('location_restriction') === 'Burkina Faso' ? 'selected' : '' }}>Burkina Faso</option>
                            <option value="Burundi" {{ request('location_restriction') === 'Burundi' ? 'selected' : '' }}>Burundi</option>
                            <option value="Cambodia" {{ request('location_restriction') === 'Cambodia' ? 'selected' : '' }}>Cambodia</option>
                            <option value="Cameroon" {{ request('location_restriction') === 'Cameroon' ? 'selected' : '' }}>Cameroon</option>
                            <option value="Canada" {{ request('location_restriction') === 'Canada' ? 'selected' : '' }}>Canada</option>
                            <option value="Cape Verde" {{ request('location_restriction') === 'Cape Verde' ? 'selected' : '' }}>Cape Verde</option>
                            <option value="Central African Republic" {{ request('location_restriction') === 'Central African Republic' ? 'selected' : '' }}>Central African Republic</option>
                            <option value="Chad" {{ request('location_restriction') === 'Chad' ? 'selected' : '' }}>Chad</option>
                            <option value="Chile" {{ request('location_restriction') === 'Chile' ? 'selected' : '' }}>Chile</option>
                            <option value="China" {{ request('location_restriction') === 'China' ? 'selected' : '' }}>China</option>
                            <option value="Colombia" {{ request('location_restriction') === 'Colombia' ? 'selected' : '' }}>Colombia</option>
                            <option value="Comoros" {{ request('location_restriction') === 'Comoros' ? 'selected' : '' }}>Comoros</option>
                            <option value="Congo" {{ request('location_restriction') === 'Congo' ? 'selected' : '' }}>Congo</option>
                            <option value="Costa Rica" {{ request('location_restriction') === 'Costa Rica' ? 'selected' : '' }}>Costa Rica</option>
                            <option value="Croatia" {{ request('location_restriction') === 'Croatia' ? 'selected' : '' }}>Croatia</option>
                            <option value="Cuba" {{ request('location_restriction') === 'Cuba' ? 'selected' : '' }}>Cuba</option>
                            <option value="Cyprus" {{ request('location_restriction') === 'Cyprus' ? 'selected' : '' }}>Cyprus</option>
                            <option value="Czech Republic" {{ request('location_restriction') === 'Czech Republic' ? 'selected' : '' }}>Czech Republic</option>
                            <option value="Denmark" {{ request('location_restriction') === 'Denmark' ? 'selected' : '' }}>Denmark</option>
                            <option value="Djibouti" {{ request('location_restriction') === 'Djibouti' ? 'selected' : '' }}>Djibouti</option>
                            <option value="Dominica" {{ request('location_restriction') === 'Dominica' ? 'selected' : '' }}>Dominica</option>
                            <option value="Dominican Republic" {{ request('location_restriction') === 'Dominican Republic' ? 'selected' : '' }}>Dominican Republic</option>
                            <option value="Ecuador" {{ request('location_restriction') === 'Ecuador' ? 'selected' : '' }}>Ecuador</option>
                            <option value="Egypt" {{ request('location_restriction') === 'Egypt' ? 'selected' : '' }}>Egypt</option>
                            <option value="El Salvador" {{ request('location_restriction') === 'El Salvador' ? 'selected' : '' }}>El Salvador</option>
                            <option value="Equatorial Guinea" {{ request('location_restriction') === 'Equatorial Guinea' ? 'selected' : '' }}>Equatorial Guinea</option>
                            <option value="Eritrea" {{ request('location_restriction') === 'Eritrea' ? 'selected' : '' }}>Eritrea</option>
                            <option value="Estonia" {{ request('location_restriction') === 'Estonia' ? 'selected' : '' }}>Estonia</option>
                            <option value="Eswatini" {{ request('location_restriction') === 'Eswatini' ? 'selected' : '' }}>Eswatini</option>
                            <option value="Ethiopia" {{ request('location_restriction') === 'Ethiopia' ? 'selected' : '' }}>Ethiopia</option>
                            <option value="Fiji" {{ request('location_restriction') === 'Fiji' ? 'selected' : '' }}>Fiji</option>
                            <option value="Finland" {{ request('location_restriction') === 'Finland' ? 'selected' : '' }}>Finland</option>
                            <option value="France" {{ request('location_restriction') === 'France' ? 'selected' : '' }}>France</option>
                            <option value="Gabon" {{ request('location_restriction') === 'Gabon' ? 'selected' : '' }}>Gabon</option>
                            <option value="Gambia" {{ request('location_restriction') === 'Gambia' ? 'selected' : '' }}>Gambia</option>
                            <option value="Georgia" {{ request('location_restriction') === 'Georgia' ? 'selected' : '' }}>Georgia</option>
                            <option value="Germany" {{ request('location_restriction') === 'Germany' ? 'selected' : '' }}>Germany</option>
                            <option value="Ghana" {{ request('location_restriction') === 'Ghana' ? 'selected' : '' }}>Ghana</option>
                            <option value="Greece" {{ request('location_restriction') === 'Greece' ? 'selected' : '' }}>Greece</option>
                            <option value="Grenada" {{ request('location_restriction') === 'Grenada' ? 'selected' : '' }}>Grenada</option>
                            <option value="Guatemala" {{ request('location_restriction') === 'Guatemala' ? 'selected' : '' }}>Guatemala</option>
                            <option value="Guinea" {{ request('location_restriction') === 'Guinea' ? 'selected' : '' }}>Guinea</option>
                            <option value="Guinea-Bissau" {{ request('location_restriction') === 'Guinea-Bissau' ? 'selected' : '' }}>Guinea-Bissau</option>
                            <option value="Guyana" {{ request('location_restriction') === 'Guyana' ? 'selected' : '' }}>Guyana</option>
                            <option value="Haiti" {{ request('location_restriction') === 'Haiti' ? 'selected' : '' }}>Haiti</option>
                            <option value="Honduras" {{ request('location_restriction') === 'Honduras' ? 'selected' : '' }}>Honduras</option>
                            <option value="Hungary" {{ request('location_restriction') === 'Hungary' ? 'selected' : '' }}>Hungary</option>
                            <option value="Iceland" {{ request('location_restriction') === 'Iceland' ? 'selected' : '' }}>Iceland</option>
                            <option value="India" {{ request('location_restriction') === 'India' ? 'selected' : '' }}>India</option>
                            <option value="Indonesia" {{ request('location_restriction') === 'Indonesia' ? 'selected' : '' }}>Indonesia</option>
                            <option value="Iran" {{ request('location_restriction') === 'Iran' ? 'selected' : '' }}>Iran</option>
                            <option value="Iraq" {{ request('location_restriction') === 'Iraq' ? 'selected' : '' }}>Iraq</option>
                            <option value="Ireland" {{ request('location_restriction') === 'Ireland' ? 'selected' : '' }}>Ireland</option>
                            <option value="Israel" {{ request('location_restriction') === 'Israel' ? 'selected' : '' }}>Israel</option>
                            <option value="Italy" {{ request('location_restriction') === 'Italy' ? 'selected' : '' }}>Italy</option>
                            <option value="Jamaica" {{ request('location_restriction') === 'Jamaica' ? 'selected' : '' }}>Jamaica</option>
                            <option value="Japan" {{ request('location_restriction') === 'Japan' ? 'selected' : '' }}>Japan</option>
                            <option value="Jordan" {{ request('location_restriction') === 'Jordan' ? 'selected' : '' }}>Jordan</option>
                            <option value="Kazakhstan" {{ request('location_restriction') === 'Kazakhstan' ? 'selected' : '' }}>Kazakhstan</option>
                            <option value="Kenya" {{ request('location_restriction') === 'Kenya' ? 'selected' : '' }}>Kenya</option>
                            <option value="Kiribati" {{ request('location_restriction') === 'Kiribati' ? 'selected' : '' }}>Kiribati</option>
                            <option value="Kosovo" {{ request('location_restriction') === 'Kosovo' ? 'selected' : '' }}>Kosovo*</option>
                            <option value="Kuwait" {{ request('location_restriction') === 'Kuwait' ? 'selected' : '' }}>Kuwait</option>
                            <option value="Kyrgyzstan" {{ request('location_restriction') === 'Kyrgyzstan' ? 'selected' : '' }}>Kyrgyzstan</option>
                            <option value="Laos" {{ request('location_restriction') === 'Laos' ? 'selected' : '' }}>Laos</option>
                            <option value="Latvia" {{ request('location_restriction') === 'Latvia' ? 'selected' : '' }}>Latvia</option>
                            <option value="Lebanon" {{ request('location_restriction') === 'Lebanon' ? 'selected' : '' }}>Lebanon</option>
                            <option value="Lesotho" {{ request('location_restriction') === 'Lesotho' ? 'selected' : '' }}>Lesotho</option>
                            <option value="Liberia" {{ request('location_restriction') === 'Liberia' ? 'selected' : '' }}>Liberia</option>
                            <option value="Libya" {{ request('location_restriction') === 'Libya' ? 'selected' : '' }}>Libya</option>
                            <option value="Liechtenstein" {{ request('location_restriction') === 'Liechtenstein' ? 'selected' : '' }}>Liechtenstein</option>
                            <option value="Lithuania" {{ request('location_restriction') === 'Lithuania' ? 'selected' : '' }}>Lithuania</option>
                            <option value="Luxembourg" {{ request('location_restriction') === 'Luxembourg' ? 'selected' : '' }}>Luxembourg</option>
                            <option value="Madagascar" {{ request('location_restriction') === 'Madagascar' ? 'selected' : '' }}>Madagascar</option>
                            <option value="Malawi" {{ request('location_restriction') === 'Malawi' ? 'selected' : '' }}>Malawi</option>
                            <option value="Malaysia" {{ request('location_restriction') === 'Malaysia' ? 'selected' : '' }}>Malaysia</option>
                            <option value="Maldives" {{ request('location_restriction') === 'Maldives' ? 'selected' : '' }}>Maldives</option>
                            <option value="Mali" {{ request('location_restriction') === 'Mali' ? 'selected' : '' }}>Mali</option>
                            <option value="Malta" {{ request('location_restriction') === 'Malta' ? 'selected' : '' }}>Malta</option>
                            <option value="Marshall Islands" {{ request('location_restriction') === 'Marshall Islands' ? 'selected' : '' }}>Marshall Islands</option>
                            <option value="Mauritania" {{ request('location_restriction') === 'Mauritania' ? 'selected' : '' }}>Mauritania</option>
                            <option value="Mauritius" {{ request('location_restriction') === 'Mauritius' ? 'selected' : '' }}>Mauritius</option>
                            <option value="Mexico" {{ request('location_restriction') === 'Mexico' ? 'selected' : '' }}>Mexico</option>
                            <option value="Micronesia" {{ request('location_restriction') === 'Micronesia' ? 'selected' : '' }}>Micronesia</option>
                            <option value="Moldova" {{ request('location_restriction') === 'Moldova' ? 'selected' : '' }}>Moldova</option>
                            <option value="Monaco" {{ request('location_restriction') === 'Monaco' ? 'selected' : '' }}>Monaco</option>
                            <option value="Mongolia" {{ request('location_restriction') === 'Mongolia' ? 'selected' : '' }}>Mongolia</option>
                            <option value="Montenegro" {{ request('location_restriction') === 'Montenegro' ? 'selected' : '' }}>Montenegro</option>
                            <option value="Morocco" {{ request('location_restriction') === 'Morocco' ? 'selected' : '' }}>Morocco</option>
                            <option value="Mozambique" {{ request('location_restriction') === 'Mozambique' ? 'selected' : '' }}>Mozambique</option>
                            <option value="Myanmar" {{ request('location_restriction') === 'Myanmar' ? 'selected' : '' }}>Myanmar</option>
                            <option value="Namibia" {{ request('location_restriction') === 'Namibia' ? 'selected' : '' }}>Namibia</option>
                            <option value="Nauru" {{ request('location_restriction') === 'Nauru' ? 'selected' : '' }}>Nauru</option>
                            <option value="Nepal" {{ request('location_restriction') === 'Nepal' ? 'selected' : '' }}>Nepal</option>
                            <option value="Netherlands" {{ request('location_restriction') === 'Netherlands' ? 'selected' : '' }}>Netherlands</option>
                            <option value="New Zealand" {{ request('location_restriction') === 'New Zealand' ? 'selected' : '' }}>New Zealand</option>
                            <option value="Nicaragua" {{ request('location_restriction') === 'Nicaragua' ? 'selected' : '' }}>Nicaragua</option>
                            <option value="Niger" {{ request('location_restriction') === 'Niger' ? 'selected' : '' }}>Niger</option>
                            <option value="Nigeria" {{ request('location_restriction') === 'Nigeria' ? 'selected' : '' }}>Nigeria</option>
                            <option value="North Korea" {{ request('location_restriction') === 'North Korea' ? 'selected' : '' }}>North Korea</option>
                            <option value="North Macedonia" {{ request('location_restriction') === 'North Macedonia' ? 'selected' : '' }}>North Macedonia</option>
                            <option value="Norway" {{ request('location_restriction') === 'Norway' ? 'selected' : '' }}>Norway</option>
                            <option value="Oman" {{ request('location_restriction') === 'Oman' ? 'selected' : '' }}>Oman</option>
                            <option value="Pakistan" {{ request('location_restriction') === 'Pakistan' ? 'selected' : '' }}>Pakistan</option>
                            <option value="Palau" {{ request('location_restriction') === 'Palau' ? 'selected' : '' }}>Palau</option>
                            <option value="Palestine" {{ request('location_restriction') === 'Palestine' ? 'selected' : '' }}>Palestine</option>
                            <option value="Panama" {{ request('location_restriction') === 'Panama' ? 'selected' : '' }}>Panama</option>
                            <option value="Papua New Guinea" {{ request('location_restriction') === 'Papua New Guinea' ? 'selected' : '' }}>Papua New Guinea</option>
                            <option value="Paraguay" {{ request('location_restriction') === 'Paraguay' ? 'selected' : '' }}>Paraguay</option>
                            <option value="Peru" {{ request('location_restriction') === 'Peru' ? 'selected' : '' }}>Peru</option>
                            <option value="Philippines" {{ request('location_restriction') === 'Philippines' ? 'selected' : '' }}>Philippines</option>
                            <option value="Poland" {{ request('location_restriction') === 'Poland' ? 'selected' : '' }}>Poland</option>
                            <option value="Portugal" {{ request('location_restriction') === 'Portugal' ? 'selected' : '' }}>Portugal</option>
                            <option value="Qatar" {{ request('location_restriction') === 'Qatar' ? 'selected' : '' }}>Qatar</option>
                            <option value="Romania" {{ request('location_restriction') === 'Romania' ? 'selected' : '' }}>Romania</option>
                            <option value="Russia" {{ request('location_restriction') === 'Russia' ? 'selected' : '' }}>Russia</option>
                            <option value="Rwanda" {{ request('location_restriction') === 'Rwanda' ? 'selected' : '' }}>Rwanda</option>
                            <option value="Saint Kitts and Nevis" {{ request('location_restriction') === 'Saint Kitts and Nevis' ? 'selected' : '' }}>Saint Kitts and Nevis</option>
                            <option value="Saint Lucia" {{ request('location_restriction') === 'Saint Lucia' ? 'selected' : '' }}>Saint Lucia</option>
                            <option value="Saint Vincent and the Grenadines" {{ request('location_restriction') === 'Saint Vincent and the Grenadines' ? 'selected' : '' }}>Saint Vincent and the Grenadines</option>
                            <option value="Samoa" {{ request('location_restriction') === 'Samoa' ? 'selected' : '' }}>Samoa</option>
                            <option value="San Marino" {{ request('location_restriction') === 'San Marino' ? 'selected' : '' }}>San Marino</option>
                            <option value="Sao Tome and Principe" {{ request('location_restriction') === 'Sao Tome and Principe' ? 'selected' : '' }}>Sao Tome and Principe</option>
                            <option value="Saudi Arabia" {{ request('location_restriction') === 'Saudi Arabia' ? 'selected' : '' }}>Saudi Arabia</option>
                            <option value="Senegal" {{ request('location_restriction') === 'Senegal' ? 'selected' : '' }}>Senegal</option>
                            <option value="Serbia" {{ request('location_restriction') === 'Serbia' ? 'selected' : '' }}>Serbia</option>
                            <option value="Seychelles" {{ request('location_restriction') === 'Seychelles' ? 'selected' : '' }}>Seychelles</option>
                            <option value="Sierra Leone" {{ request('location_restriction') === 'Sierra Leone' ? 'selected' : '' }}>Sierra Leone</option>
                            <option value="Singapore" {{ request('location_restriction') === 'Singapore' ? 'selected' : '' }}>Singapore</option>
                            <option value="Slovakia" {{ request('location_restriction') === 'Slovakia' ? 'selected' : '' }}>Slovakia</option>
                            <option value="Slovenia" {{ request('location_restriction') === 'Slovenia' ? 'selected' : '' }}>Slovenia</option>
                            <option value="Solomon Islands" {{ request('location_restriction') === 'Solomon Islands' ? 'selected' : '' }}>Solomon Islands</option>
                            <option value="Somalia" {{ request('location_restriction') === 'Somalia' ? 'selected' : '' }}>Somalia</option>
                            <option value="South Africa" {{ request('location_restriction') === 'South Africa' ? 'selected' : '' }}>South Africa</option>
                            <option value="South Korea" {{ request('location_restriction') === 'South Korea' ? 'selected' : '' }}>South Korea</option>
                            <option value="South Sudan" {{ request('location_restriction') === 'South Sudan' ? 'selected' : '' }}>South Sudan</option>
                            <option value="Spain" {{ request('location_restriction') === 'Spain' ? 'selected' : '' }}>Spain</option>
                            <option value="Sri Lanka" {{ request('location_restriction') === 'Sri Lanka' ? 'selected' : '' }}>Sri Lanka</option>
                            <option value="Sudan" {{ request('location_restriction') === 'Sudan' ? 'selected' : '' }}>Sudan</option>
                            <option value="Suriname" {{ request('location_restriction') === 'Suriname' ? 'selected' : '' }}>Suriname</option>
                            <option value="Sweden" {{ request('location_restriction') === 'Sweden' ? 'selected' : '' }}>Sweden</option>
                            <option value="Switzerland" {{ request('location_restriction') === 'Switzerland' ? 'selected' : '' }}>Switzerland</option>
                            <option value="Syria" {{ request('location_restriction') === 'Syria' ? 'selected' : '' }}>Syria</option>
                            <option value="Taiwan" {{ request('location_restriction') === 'Taiwan' ? 'selected' : '' }}>Taiwan</option>
                            <option value="Tajikistan" {{ request('location_restriction') === 'Tajikistan' ? 'selected' : '' }}>Tajikistan</option>
                            <option value="Tanzania" {{ request('location_restriction') === 'Tanzania' ? 'selected' : '' }}>Tanzania</option>
                            <option value="Thailand" {{ request('location_restriction') === 'Thailand' ? 'selected' : '' }}>Thailand</option>
                            <option value="Timor-Leste" {{ request('location_restriction') === 'Timor-Leste' ? 'selected' : '' }}>Timor-Leste</option>
                            <option value="Togo" {{ request('location_restriction') === 'Togo' ? 'selected' : '' }}>Togo</option>
                            <option value="Tonga" {{ request('location_restriction') === 'Tonga' ? 'selected' : '' }}>Tonga</option>
                            <option value="Trinidad and Tobago" {{ request('location_restriction') === 'Trinidad and Tobago' ? 'selected' : '' }}>Trinidad and Tobago</option>
                            <option value="Tunisia" {{ request('location_restriction') === 'Tunisia' ? 'selected' : '' }}>Tunisia</option>
                            <option value="Turkey" {{ request('location_restriction') === 'Turkey' ? 'selected' : '' }}>Turkey</option>
                            <option value="Turkmenistan" {{ request('location_restriction') === 'Turkmenistan' ? 'selected' : '' }}>Turkmenistan</option>
                            <option value="Tuvalu" {{ request('location_restriction') === 'Tuvalu' ? 'selected' : '' }}>Tuvalu</option>
                            <option value="Uganda" {{ request('location_restriction') === 'Uganda' ? 'selected' : '' }}>Uganda</option>
                            <option value="Ukraine" {{ request('location_restriction') === 'Ukraine' ? 'selected' : '' }}>Ukraine</option>
                            <option value="United Arab Emirates" {{ request('location_restriction') === 'United Arab Emirates' ? 'selected' : '' }}>United Arab Emirates</option>
                            <option value="United Kingdom" {{ request('location_restriction') === 'United Kingdom' ? 'selected' : '' }}>United Kingdom</option>
                            <option value="Uruguay" {{ request('location_restriction') === 'Uruguay' ? 'selected' : '' }}>Uruguay</option>
                            <option value="Uzbekistan" {{ request('location_restriction') === 'Uzbekistan' ? 'selected' : '' }}>Uzbekistan</option>
                            <option value="Vanuatu" {{ request('location_restriction') === 'Vanuatu' ? 'selected' : '' }}>Vanuatu</option>
                            <option value="Vatican City" {{ request('location_restriction') === 'Vatican City' ? 'selected' : '' }}>Vatican City</option>
                            <option value="Venezuela" {{ request('location_restriction') === 'Venezuela' ? 'selected' : '' }}>Venezuela</option>
                            <option value="Vietnam" {{ request('location_restriction') === 'Vietnam' ? 'selected' : '' }}>Vietnam</option>
                            <option value="Yemen" {{ request('location_restriction') === 'Yemen' ? 'selected' : '' }}>Yemen</option>
                            <option value="Zambia" {{ request('location_restriction') === 'Zambia' ? 'selected' : '' }}>Zambia</option>
                            <option value="Zimbabwe" {{ request('location_restriction') === 'Zimbabwe' ? 'selected' : '' }}>Zimbabwe</option>
                        </select>
                    </div>
                </div>

                @if(request()->hasAny(['search', 'technology', 'seniority', 'remote_type']))
                    <div class="flex items-center justify-between pt-4 border-t border-border">
                        <span class="text-sm text-muted-foreground">
                            Showing {{ $positions->firstItem() }}-{{ $positions->lastItem() }} of {{ $positions->total() }} results
                        </span>
                        <a href="/positions" class="text-sm text-primary hover:underline">Clear filters</a>
                    </div>
                @endif
            </form>
        </div>

        <!-- Position Cards -->
        <div class="space-y-4">
            @forelse($positions as $position)
                <a href="/positions/{{ $position->slug }}" class="block">
                    <div class="rounded-lg shadow hover:shadow-lg transition-all duration-300 p-6 {{ in_array($position->listing_type, [ListingType::Featured, ListingType::Top]) ? 'border-2 border-primary bg-gradient-to-br from-primary/10 via-primary/5 to-card shadow-lg shadow-primary/30' : 'bg-card border border-border hover:border-primary' }}">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                            <div class="flex-1 space-y-2">
                                <!-- Company Logo and Title -->
                                <div class="flex items-start {{ in_array($position->listing_type, [ListingType::Featured, ListingType::Top]) ? 'gap-4' : '' }}">
                                    @if(in_array($position->listing_type, [ListingType::Featured, ListingType::Top]))
                                        @if($position->company->logo)
                                            <img src="{{ asset('storage/' . $position->company->logo) }}" alt="{{ $position->company->name }}" class="w-12 h-12 rounded-lg object-cover">
                                        @else
                                            <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-primary to-accent flex items-center justify-center text-primary-foreground font-bold text-xl">
                                                {{ substr($position->company->name, 0, 1) }}
                                            </div>
                                        @endif
                                    @endif

                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <h3 class="{{ $position->listing_type === ListingType::Top ? 'text-3xl' : 'text-xl' }} font-semibold text-foreground hover:text-primary transition-colors">
                                                {{ $position->title }}
                                            </h3>
                                            @if($position->listing_type === ListingType::Top)
                                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-primary/20 text-primary border border-primary">
                                                    ⭐ Top
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-muted-foreground">{{ $position->company->name }}</p>
                                    </div>
                                </div>

                                <!-- Description -->
                                <p class="text-muted-foreground line-clamp-2">
                                    {{ $position->short_description }}
                                </p>

                                <!-- Meta Information -->
                                <div class="flex flex-wrap items-center gap-4 text-sm text-muted-foreground">
                                    @if($position->seniority)
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            {{ ucfirst($position->seniority) }}
                                        </span>
                                    @endif

                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        @if ($position->remote_type !== 'global' )
                                            {{ $position->location_restriction }}
                                        @else
                                            <strong>Global</strong>
                                        @endif
                                    </span>

                                    @if($position->salary_min && $position->salary_max)
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            ${{ number_format($position->salary_min) }} - ${{ number_format($position->salary_max) }}
                                        </span>
                                    @endif

                                    <span class="text-muted-foreground" title="Published on {{ $position->published_at->format('M d, Y \a\t g:i A T') }}">
                                        {{ $position->published_at->diffForHumans() }}
                                    </span>
                                </div>

                                <!-- Technologies -->
                                @if($position->technologies->isNotEmpty())
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($position->technologies->take(5) as $tech)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-accent text-accent-foreground">
                                                {{ $tech->name }}
                                            </span>
                                        @endforeach
                                        @if($position->technologies->count() > 5)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-muted text-muted-foreground">
                                                +{{ $position->technologies->count() - 5 }} more
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <!-- Apply Button -->
                            <div class="flex items-center">
                                <span class="inline-flex items-center px-4 py-2 bg-primary hover:bg-primary/90 text-primary-foreground text-sm font-medium rounded-lg transition-colors">
                                    View Details
                                    <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="bg-card rounded-lg shadow p-12 text-center border border-border transition-colors duration-300">
                    <svg class="mx-auto h-12 w-12 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <h3 class="mt-2 text-lg font-medium text-foreground">No positions found</h3>
                    <p class="mt-1 text-muted-foreground">Try adjusting your search or filter criteria.</p>
                    <a href="/positions" class="mt-4 inline-block text-primary hover:underline">Clear all filters</a>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($positions->hasPages())
            <div class="mt-8">
                {{ $positions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

