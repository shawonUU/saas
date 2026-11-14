<?php $__env->startSection('content'); ?>

<style>
    .register-page {
        min-height: calc(100vh - 150px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 55px 20px;
        background: transparent;
    }

    .register-wrapper {
        width: 100%;
        max-width: 440px;
        margin: 0 auto;
    }

    /* Header */

    .register-heading {
        margin-bottom: 34px;
        text-align: center;
    }

    .register-heading-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 17px;
        color: #2563eb;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 1.5px;
        text-transform: uppercase;
    }

    .register-heading-label span {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #2563eb;
    }

    .register-heading h1 {
        margin: 0 0 12px;
        color: var(--text, #111827);
        font-size: 32px;
        line-height: 1.25;
        font-weight: 800;
        letter-spacing: -1.2px;
    }

    .register-heading p {
        max-width: 360px;
        margin: 0 auto;
        color: var(--muted, #6b7280);
        font-size: 13px;
        line-height: 1.8;
    }

    /* Form */

    .register-form {
        display: grid;
        gap: 21px;
    }

    .register-field label {
        display: block;
        margin-bottom: 9px;
        color: var(--text, #374151);
        font-size: 12px;
        font-weight: 700;
    }

    .register-input-wrap {
        position: relative;
    }

    .register-input {
        width: 100%;
        height: 50px;
        padding: 0 15px;
        border: 1px solid var(--border, #e5e7eb);
        border-radius: 9px;
        outline: none;
        background: var(--input-bg, #ffffff);
        color: var(--text, #111827);
        font-family: inherit;
        font-size: 13px;
        transition: border-color .2s, box-shadow .2s;
        box-sizing: border-box;
    }

    .register-input::placeholder {
        color: var(--muted, #9ca3af);
        opacity: .8;
    }

    .register-input:hover {
        border-color: #a5b4fc;
    }

    .register-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, .10);
    }

    .register-input.has-toggle {
        padding-right: 65px;
    }

    .password-toggle {
        position: absolute;
        top: 50%;
        right: 14px;
        transform: translateY(-50%);
        padding: 5px;
        border: 0;
        background: transparent;
        color: var(--muted, #6b7280);
        font-family: inherit;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
    }

    .password-toggle:hover {
        color: #2563eb;
    }

    /* Validation */

    .register-error {
        margin-top: 7px;
        color: #dc2626;
        font-size: 11px;
        line-height: 1.5;
    }

    /* Submit Button */

    .register-btn {
        width: 100%;
        height: 50px;
        margin-top: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        border: none;
        border-radius: 9px;
        background: #2563eb;
        color: #ffffff;
        font-family: inherit;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
        transition: background .2s, transform .2s, box-shadow .2s;
    }

    .register-btn:hover {
        background: #1d4ed8;
        transform: translateY(-1px);
        box-shadow: 0 7px 18px rgba(37, 99, 235, .18);
    }

    .register-btn:active {
        transform: translateY(0);
    }

    .register-btn-arrow {
        font-size: 17px;
        line-height: 1;
        transition: transform .2s;
    }

    .register-btn:hover .register-btn-arrow {
        transform: translateX(4px);
    }

    /* Login Link */

    .register-login {
        margin: 27px 0 0;
        text-align: center;
        color: var(--muted, #6b7280);
        font-size: 12px;
    }

    .register-login a {
        margin-left: 4px;
        color: #2563eb;
        font-weight: 800;
        text-decoration: none;
    }

    .register-login a:hover {
        text-decoration: underline;
    }

    /* Security */

    .register-security {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 35px;
        color: var(--muted, #9ca3af);
        font-size: 10px;
    }

    .register-security-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 17px;
        height: 17px;
        border-radius: 50%;
        background: rgba(34, 197, 94, .10);
        color: #16a34a;
        font-size: 10px;
    }

    /* Responsive */

    @media (max-width: 600px) {
        .register-page {
            min-height: calc(100vh - 110px);
            padding: 35px 20px;
        }

        .register-heading {
            margin-bottom: 28px;
        }

        .register-heading h1 {
            font-size: 27px;
        }

        .register-form {
            gap: 18px;
        }
    }

    @media (max-width: 360px) {
        .register-page {
            padding-right: 15px;
            padding-left: 15px;
        }

        .register-heading h1 {
            font-size: 24px;
        }
    }
</style>

<div class="register-page">
    <div class="register-wrapper">

        
        <div class="register-heading">

            <div class="register-heading-label">
                <span></span>
                <?php echo e(t('GET STARTED', 'শুরু করুন')); ?>

            </div>

            <h1><?php echo e($title); ?></h1>

        </div>

        
        <form action="<?php echo e($action); ?>" method="post" class="register-form">

            <?php echo csrf_field(); ?>

            
            <div class="register-field">

                <label for="name">
                    <?php echo e(t('Full name', 'পুরো নাম')); ?>

                </label>

                <input
                    class="register-input"
                    id="name"
                    name="name"
                    type="text"
                    value="<?php echo e(old('name')); ?>"
                    placeholder="<?php echo e(t('Enter your full name', 'আপনার পুরো নাম লিখুন')); ?>"
                    maxlength="255"
                    autocomplete="name"
                    required
                >

                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="register-error"><?php echo e($message); ?></div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

            </div>

            
            <div class="register-field">

                <label for="email">
                    <?php echo e(t('Email address', 'ইমেইল ঠিকানা')); ?>

                </label>

                <input
                    class="register-input"
                    id="email"
                    name="email"
                    type="email"
                    value="<?php echo e(old('email')); ?>"
                    placeholder="<?php echo e(t('name@example.com', 'name@example.com')); ?>"
                    autocomplete="username"
                    required
                >

                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="register-error"><?php echo e($message); ?></div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

            </div>

            
            <div class="register-field">

                <label for="password">
                    <?php echo e(t('Password', 'পাসওয়ার্ড')); ?>

                </label>

                <div class="register-input-wrap">

                    <input
                        class="register-input has-toggle"
                        id="password"
                        name="password"
                        type="password"
                        placeholder="<?php echo e(t('Minimum 8 characters', 'কমপক্ষে ৮ অক্ষর')); ?>"
                        autocomplete="new-password"
                        minlength="8"
                        required
                    >

                    <button
                        type="button"
                        class="password-toggle"
                        onclick="togglePassword('password', this)"
                    >
                        <?php echo e(t('Show', 'দেখুন')); ?>

                    </button>

                </div>

                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="register-error"><?php echo e($message); ?></div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

            </div>

            
            <div class="register-field">

                <label for="password_confirmation">
                    <?php echo e(t('Confirm password', 'পাসওয়ার্ড নিশ্চিত করুন')); ?>

                </label>

                <div class="register-input-wrap">

                    <input
                        class="register-input has-toggle"
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        placeholder="<?php echo e(t('Re-enter your password', 'আবার পাসওয়ার্ড লিখুন')); ?>"
                        autocomplete="new-password"
                        minlength="8"
                        required
                    >

                    <button
                        type="button"
                        class="password-toggle"
                        onclick="togglePassword('password_confirmation', this)"
                    >
                        <?php echo e(t('Show', 'দেখুন')); ?>

                    </button>

                </div>

            </div>

            
            <button class="register-btn" type="submit">

                <?php echo e(t('Create your account', 'অ্যাকাউন্ট তৈরি করুন')); ?>


                <span class="register-btn-arrow">→</span>

            </button>

        </form>

        
        <p class="register-login">

            <?php echo e(t('Already have an account?', 'আগে থেকেই অ্যাকাউন্ট আছে?')); ?>


            <a href="<?php echo e($loginUrl); ?>">
                <?php echo e(t('Log in', 'লগইন করুন')); ?>

            </a>

        </p>

        
        <div class="register-security">

            <span class="register-security-icon">✓</span>

            <?php echo e(t(
                'Your information is protected and secure.',
                'আপনার তথ্য নিরাপদ ও সুরক্ষিত।'
            )); ?>


        </div>

    </div>
</div>

<script>
    function togglePassword(inputId, button) {
        const input = document.getElementById(inputId);

        if (!input) {
            return;
        }

        const isPassword = input.type === 'password';

        input.type = isPassword ? 'text' : 'password';

        button.textContent = isPassword
            ? <?php echo json_encode(t('Hide', 'লুকান'), 512) ?>
            : <?php echo json_encode(t('Show', 'দেখুন'), 512) ?>;
    }
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('public.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\saas\resources\views/public/auth/register.blade.php ENDPATH**/ ?>