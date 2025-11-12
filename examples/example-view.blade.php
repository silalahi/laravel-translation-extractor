<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('My Application') }}</title>
</head>
<body>
    <nav>
        <ul>
            <li><a href="/">{{ __('Home') }}</a></li>
            <li><a href="/about">{{ __('About Us') }}</a></li>
            <li><a href="/contact">{{ __('Contact') }}</a></li>
        </ul>
    </nav>

    <main>
        <h1>{{ __('Welcome to our website') }}</h1>
        
        <section>
            <h2>{{ __('Featured Products') }}</h2>
            <p>{{ __('Check out our latest offerings.') }}</p>
        </section>

        <section>
            <h2>@lang('Customer Testimonials')</h2>
            <blockquote>
                {{ trans('This product changed my life!') }}
            </blockquote>
        </section>

        <section>
            <h2>{{ __('Get Started Today') }}</h2>
            <p>{{ __('Sign up now and get 30% off your first purchase.') }}</p>
            <button>{{ __('Sign Up') }}</button>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 {{ __('All rights reserved') }}</p>
        <p>{{ __('Follow us on social media') }}</p>
    </footer>
</body>
</html>
