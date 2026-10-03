```blade
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Account</title>

    @vite(['resources/css/app.css'])

    <style>
        :root {
            --brand: #ff5a00;
            --brand-dark: #e04d00;
            --brand-soft: #fff3ec;
            --text: #111827;
            --muted: #6b7280;
            --border: #d1d5db;
            --line: #e5e7eb;
        }

        .reg *,
        .reg *::before,
        .reg *::after {
            box-sizing: border-box;
        }

        .reg {
            min-height: 100vh;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
            color: var(--text);
        }

        .reg-card {
            width: 100%;
            max-width: 560px;
            background: #fff;
            border: 1px solid var(--line);
            border-top: 3px solid var(--brand);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .06);
            overflow: hidden;
        }

        .reg-head {
            padding: 24px 32px 0;
            text-align: center;
        }

        .reg-logo {
            height: 32px;
            width: auto;
            margin: 0 auto 10px;
            display: block;
        }

        .reg-title {
            font-size: 20px;
            font-weight: 600;
            margin: 0;
        }

        .reg-sub {
            font-size: 13px;
            color: var(--muted);
            margin: 4px 0 0;
        }

        .stepper {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 20px auto 22px;
            max-width: 450px;
        }

        .step-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .step-circle {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: var(--line);
            color: var(--muted);
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: .2s;
        }

        .step-circle.is-active {
            background: var(--brand);
            color: #fff;
        }

        .step-label {
            font-size: 12px;
            color: var(--muted);
        }

        .step-label.is-active {
            color: var(--text);
            font-weight: 600;
        }

        .step-line {
            flex: 1;
            height: 2px;
            background: var(--line);
            margin: 0 10px;
            min-width: 20px;
            border-radius: 2px;
        }

        .step-line.is-active {
            background: var(--brand);
        }

        .step {
            padding: 0 32px 24px;
        }

        .step.hidden {
            display: none;
        }

        .step-title {
            font-size: 16px;
            font-weight: 600;
            margin: 0;
        }

        .step-desc {
            font-size: 12px;
            color: var(--muted);
            margin: 2px 0 16px;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .field {
            margin-bottom: 12px;
        }

        .label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 4px;
        }

        .input {
            display: block;
            width: 100%;
            height: 38px;
            padding: 0 12px;
            font-size: 14px;
            color: var(--text);
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 8px;
            outline: none;
        }

        textarea.input {
            height: auto;
            padding: 8px 12px;
            resize: vertical;
        }

        .input:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 3px var(--brand-soft);
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap .input {
            padding-right: 52px;
        }

        .toggle-pass {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: 0;
            font-size: 12px;
            color: var(--muted);
            cursor: pointer;
        }

        .actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }

        .actions.end {
            justify-content: flex-end;
        }

        .btn {
            height: 38px;
            padding: 0 18px;
            font-size: 14px;
            font-weight: 500;
            border-radius: 8px;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--brand);
            color: #fff;
            border: 1px solid var(--brand);
        }

        .btn-primary:hover {
            background: var(--brand-dark);
        }

        .btn-ghost {
            background: #fff;
            color: #374151;
            border: 1px solid var(--border);
        }

        /* Dynamic list */

        .list-box {
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 18px;
            background: #fafafa;
        }

        .list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .list-title {
            font-size: 13px;
            font-weight: 600;
        }

        .add-btn {
            border: 1px solid var(--brand);
            background: var(--brand-soft);
            color: var(--brand-dark);
            border-radius: 7px;
            padding: 6px 10px;
            font-size: 12px;
            cursor: pointer;
        }

        .dynamic-row {
            display: flex;
            gap: 8px;
            margin-bottom: 8px;
        }

        .dynamic-row:last-child {
            margin-bottom: 0;
        }

        .dynamic-row .input {
            flex: 1;
        }

        .remove-btn {
            width: 38px;
            height: 38px;
            border: 1px solid #fecaca;
            background: #fff;
            color: #dc2626;
            border-radius: 8px;
            cursor: pointer;
        }

        .empty-text {
            font-size: 12px;
            color: var(--muted);
            text-align: center;
            padding: 8px;
        }

        /* Payment */

        .payment-card {
            border: 1px solid var(--brand);
            background: var(--brand-soft);
            border-radius: 12px;
            padding: 24px;
            text-align: center;
        }

        .payment-label {
            font-size: 12px;
            color: var(--muted);
        }

        .payment-price {
            font-size: 34px;
            font-weight: 700;
            margin: 5px 0;
        }

        .payment-note {
            font-size: 12px;
            color: var(--muted);
        }

        .feature-list {
            text-align: left;
            margin: 18px auto 0;
            max-width: 260px;
            font-size: 13px;
            color: #4b5563;
        }

        .feature-list div {
            margin-bottom: 7px;
        }

        .reg-foot {
            border-top: 1px solid var(--line);
            padding: 14px 16px;
            text-align: center;
            font-size: 13px;
            color: var(--muted);
            background: #fafafa;
        }

        .reg-foot a {
            color: var(--brand);
            font-weight: 500;
            text-decoration: none;
        }

        @media (max-width: 520px) {

            .reg-head,
            .step {
                padding-left: 20px;
                padding-right: 20px;
            }

            .grid-2 {
                grid-template-columns: 1fr;
            }

            .step-label {
                display: none;
            }
        }
    </style>
</head>

<body>

<div class="reg">

    <div class="reg-card">

        <!-- HEADER -->

        <div class="reg-head">

            <img
                src="{{ asset('images/uest_logo.png') }}"
                alt="UEST Logo"
                class="reg-logo"
            >

            <h1 class="reg-title">
                Create your account
            </h1>

            <p class="reg-sub">
                Set up your organization in a few simple steps
            </p>

            <!-- STEPPER -->

            <div class="stepper">

                <div class="step-item" data-step="1">
                    <div class="step-circle is-active">1</div>
                    <span class="step-label is-active">Personal</span>
                </div>

                <div class="step-line"></div>

                <div class="step-item" data-step="2">
                    <div class="step-circle">2</div>
                    <span class="step-label">Company</span>
                </div>

                <div class="step-line"></div>

                <div class="step-item" data-step="3">
                    <div class="step-circle">3</div>
                    <span class="step-label">Setup</span>
                </div>

            </div>

        </div>


        <form method="POST" action="{{ route('company.register') }}" id="registrationForm">

            @csrf


            <!-- ================= STEP 1 ================= -->

            <div class="step" id="step1">

                <h2 class="step-title">
                    Personal information
                </h2>

                <p class="step-desc">
                    Enter the details of the account owner.
                </p>


                <div class="grid-2">

                    <div class="field">
                        <label class="label">First Name</label>

                        <input
                            type="text"
                            name="first_name"
                            required
                            class="input"
                            placeholder="John">
                    </div>

                    <div class="field">
                        <label class="label">Last Name</label>

                        <input
                            type="text"
                            name="last_name"
                            required
                            class="input"
                            placeholder="Doe">
                    </div>

                </div>


                <div class="field">

                    <label class="label">
                        Email Address
                    </label>

                    <input
                        type="email"
                        name="email"
                        required
                        class="input"
                        placeholder="john@company.com">

                </div>


                <div class="field">

                    <label class="label">
                        Mobile Number
                    </label>

                    <input
                        type="tel"
                        name="mobile"
                        maxlength="10"
                        required
                        class="input"
                        placeholder="9876543210">

                </div>


                <div class="grid-2">

                    <div class="field">

                        <label class="label">
                            Password
                        </label>

                        <div class="input-wrap">

                            <input
                                type="password"
                                name="password"
                                id="password"
                                required
                                class="input"
                                placeholder="Minimum 8 characters">

                            <button
                                type="button"
                                onclick="togglePassword('password', this)"
                                class="toggle-pass">
                                Show
                            </button>

                        </div>

                    </div>


                    <div class="field">

                        <label class="label">
                            Confirm Password
                        </label>

                        <input
                            type="password"
                            name="password_confirmation"
                            required
                            class="input"
                            placeholder="Confirm password">

                    </div>

                </div>


                <div class="actions end">

                    <button
                        type="button"
                        onclick="nextStep()"
                        class="btn btn-primary">
                        Continue →
                    </button>

                </div>

            </div>


            <!-- ================= STEP 2 ================= -->

            <div class="step hidden" id="step2">

                <h2 class="step-title">
                    Company information
                </h2>

                <p class="step-desc">
                    Tell us about your organization.
                </p>


                <div class="field">

                    <label class="label">
                        Company Name
                    </label>

                    <input
                        type="text"
                        name="company_name"
                        required
                        class="input"
                        placeholder="ABC Industries">

                </div>


                <div class="grid-2">

                    <div class="field">

                        <label class="label">
                            Company Type
                        </label>

                        <select
                            name="company_type"
                            required
                            class="input">

                            <option value="">
                                Select type
                            </option>

                            <option value="factory">
                                Factory
                            </option>

                            <option value="it">
                                IT / Software
                            </option>

                            <option value="school">
                                School
                            </option>

                            <option value="hospital">
                                Hospital
                            </option>

                            <option value="retail">
                                Retail
                            </option>

                            <option value="other">
                                Other
                            </option>

                        </select>

                    </div>


                    <div class="field">

                        <label class="label">
                            Number of Employees
                        </label>

                        <input
                            type="number"
                            name="employee_count"
                            min="1"
                            required
                            class="input"
                            placeholder="50">

                    </div>

                </div>


                <div class="grid-2">

                    <div class="field">

                        <label class="label">
                            State
                        </label>

                        <input
                            type="text"
                            name="state"
                            required
                            class="input"
                            placeholder="Gujarat">

                    </div>


                    <div class="field">

                        <label class="label">
                            City
                        </label>

                        <input
                            type="text"
                            name="city"
                            required
                            class="input"
                            placeholder="Rajkot">

                    </div>

                </div>


                <div class="field">

                    <label class="label">
                        Company Address
                    </label>

                    <textarea
                        name="address"
                        rows="2"
                        required
                        class="input"
                        placeholder="Enter complete company address"></textarea>

                </div>


                <div class="actions">

                    <button
                        type="button"
                        onclick="previousStep()"
                        class="btn btn-ghost">
                        ← Back
                    </button>

                    <button
                        type="button"
                        onclick="nextStep()"
                        class="btn btn-primary">
                        Continue →
                    </button>

                </div>

            </div>


            <!-- ================= STEP 3 ================= -->
<!-- ================= STEP 3 ================= -->
<div class="step hidden" id="step3">

    <h2 class="step-title">
        Employees & Branches
    </h2>

    <p class="step-desc">
        Set up your employee types and company branches.
    </p>

    <!-- EMPLOYEE TYPES -->
    <div class="list-box">

        <div class="list-header">
            <span class="list-title">
                Employee Types
            </span>

            <button
                type="button"
                onclick="addEmployeeType()"
                class="add-btn">
                + Add Type
            </button>
        </div>

        <div id="employeeTypes">

            <div class="dynamic-row">

                <input
                    type="text"
                    name="employee_types[]"
                    class="input"
                    placeholder="Example: Worker"
                    required>

                <button
                    type="button"
                    onclick="removeRow(this)"
                    class="remove-btn">
                    ×
                </button>

            </div>

        </div>

    </div>

    <!-- BRANCHES -->
    <div class="list-box">

        <div class="list-header">
            <span class="list-title">
                Company Branches
            </span>

            <button
                type="button"
                onclick="addBranch()"
                class="add-btn">
                + Add Branch
            </button>
        </div>

        <div id="branches">

            <div class="dynamic-row">

                <input
                    type="text"
                    name="branches[]"
                    class="input"
                    placeholder="Example: Rajkot Branch"
                    required>

                <button
                    type="button"
                    onclick="removeRow(this)"
                    class="remove-btn">
                    ×
                </button>

            </div>

        </div>

    </div>

    <div class="actions">

        <button
            type="button"
            onclick="previousStep()"
            class="btn btn-ghost">
            ← Back
        </button>

        <button
            type="submit"
            class="btn btn-primary">
            Create Company
        </button>

    </div>

</div>
        </form>


        <div class="reg-foot">

            Already have an account?

            <a href="{{ route('login') }}">
                Login
            </a>

        </div>

    </div>

</div>


<script>

    let currentStep = 1;


    function showStep(step) {

        document.querySelectorAll('.step').forEach(function(element) {
            element.classList.add('hidden');
        });

        document.getElementById('step' + step)
            .classList.remove('hidden');

        currentStep = step;

        updateProgress();
    }


    function updateProgress() {

        document.querySelectorAll('.step-item').forEach(function(item) {

            const number = parseInt(item.dataset.step);

            const circle = item.querySelector('.step-circle');

            const label = item.querySelector('.step-label');

            circle.classList.toggle(
                'is-active',
                number <= currentStep
            );

            label.classList.toggle(
                'is-active',
                number === currentStep
            );

        });


        document.querySelectorAll('.step-line')
            .forEach(function(line, index) {

                line.classList.toggle(
                    'is-active',
                    index < currentStep - 1
                );

            });

    }


    function validateCurrentStep() {

        const step =
            document.getElementById('step' + currentStep);

        const inputs =
            step.querySelectorAll(
                'input[required], select[required], textarea[required]'
            );


        for (const input of inputs) {

            if (!input.checkValidity()) {

                input.reportValidity();

                return false;

            }

        }


        if (currentStep === 1) {

            const password =
                document.querySelector(
                    'input[name="password"]'
                ).value;

            const confirmation =
                document.querySelector(
                    'input[name="password_confirmation"]'
                ).value;


            if (password !== confirmation) {

                alert('Passwords do not match.');

                return false;

            }

        }


        return true;

    }


    function nextStep() {

        if (!validateCurrentStep()) {
            return;
        }


        if (currentStep < 4) {

            showStep(currentStep + 1);

        }

    }


    function previousStep() {

        if (currentStep > 1) {

            showStep(currentStep - 1);

        }

    }


    function togglePassword(id, button) {

        const input =
            document.getElementById(id);


        if (input.type === 'password') {

            input.type = 'text';

            button.textContent = 'Hide';

        } else {

            input.type = 'password';

            button.textContent = 'Show';

        }

    }


    function addEmployeeType() {

        const container =
            document.getElementById('employeeTypes');


        const row =
            document.createElement('div');

        row.className = 'dynamic-row';


        row.innerHTML = `
            <input
                type="text"
                name="employee_types[]"
                class="input"
                placeholder="Example: Manager"
                required>

            <button
                type="button"
                onclick="removeRow(this)"
                class="remove-btn">
                ×
            </button>
        `;


        container.appendChild(row);

    }


    function addBranch() {

        const container =
            document.getElementById('branches');


        const row =
            document.createElement('div');

        row.className = 'dynamic-row';


        row.innerHTML = `
            <input
                type="text"
                name="branches[]"
                class="input"
                placeholder="Example: Ahmedabad Branch"
                required>

            <button
                type="button"
                onclick="removeRow(this)"
                class="remove-btn">
                ×
            </button>
        `;


        container.appendChild(row);

    }


    function removeRow(button) {

        const container =
            button.parentElement.parentElement;


        const rows =
            container.querySelectorAll('.dynamic-row');


        if (rows.length <= 1) {

            alert('At least one item is required.');

            return;

        }


        button.parentElement.remove();

    }


    showStep(1);

</script>

</body>

</html>

