<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="ChatBot Nepal — AI chatbots for Nepali businesses. Answer customer questions 24/7 in Nepali, Hindi & English. Setup in 24 hours. Rs. 999/month.">
<title>ChatBot Nepal — AI Chatbot for Your Business Website</title>
<link rel="canonical" href="{{ config('app.url') }}">
<meta property="og:title" content="ChatBot Nepal — AI Chatbot for Your Business Website">
<meta property="og:description" content="Answer customer queries 24/7 in Nepali, Hindi & English. No coding. Setup in 24 hours.">
<meta property="og:url" content="{{ config('app.url') }}">
<meta property="og:type" content="website">
<meta property="og:image" content="{{ asset('images/og-preview.png') }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="ChatBot Nepal — AI Chatbot for Nepali Businesses">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:image" content="{{ asset('images/og-preview.png') }}">
<meta name="keywords" content="AI chatbot Nepal, chatbot for Nepali business, Nepali language chatbot, website automation Nepal, 24/7 customer support Nepal, AI chatbot Kathmandu, Nepali Hindi English chatbot, SME chatbot Nepal">

<script type="application/ld+json">
@verbatim
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": ["Organization", "LocalBusiness"],
      "@id": "https://chatbotnepal.isoftroerp.com/#organization",
      "name": "ChatBot Nepal",
      "url": "https://chatbotnepal.isoftroerp.com",
      "telephone": "+9779811144402",
      "email": "info@isoftro.com",
      "address": {
        "@type": "PostalAddress",
        "addressLocality": "Kathmandu",
        "addressRegion": "Bagmati",
        "addressCountry": "NP"
      },
      "areaServed": { "@type": "Country", "name": "Nepal" },
      "description": "AI chatbot service for Nepali SMEs — coaching centers, clinics, hotels, retail stores",
      "priceRange": "Rs. 999/month",
      "sameAs": []
    },
    {
      "@type": "FAQPage",
      "mainEntity": [
        {"@type": "Question","name": "How long does setup take?","acceptedAnswer": {"@type": "Answer","text": "24 hours from our interview call. We collect your info, build the knowledge base, and deploy. You paste one line of code."}},
        {"@type": "Question","name": "Does it understand Nepali in Roman letters?","acceptedAnswer": {"@type": "Answer","text": "Yes. Customers can type 'fee kati ho?' in romanized Nepali. The chatbot understands and replies perfectly."}},
        {"@type": "Question","name": "Is there a contract?","acceptedAnswer": {"@type": "Answer","text": "No. Month-to-month. Cancel anytime. No lock-in."}}
      ]
    }
  ]
}
@endverbatim
</script>

<script type="application/ld+json">
@verbatim
{
  "@context": "https://schema.org",
  "@type": "Service",
  "name": "ChatBot Nepal AI Chatbot Service",
  "description": "Custom AI chatbot service for business websites in Nepal. Answers customers in Nepali, Hindi, and English 24/7.",
  "provider": {"@id": "https://chatbotnepal.isoftroerp.com/#organization"},
  "areaServed": {"@type": "Country", "name": "Nepal"},
  "offers": [
    {"@type": "Offer","name": "Starter Plan","price": "999","priceCurrency": "NPR",
     "priceSpecification": [
       {"@type": "UnitPriceSpecification","price": "999","priceCurrency": "NPR","billingIncrement": 1,"unitCode": "MON","name": "Monthly Subscription"},
       {"@type": "UnitPriceSpecification","price": "3000","priceCurrency": "NPR","billingIncrement": 1,"unitCode": "C62","name": "One-time Setup Fee"}
     ]}
  ]
}
@endverbatim
</script>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0">

<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          'brand': '#18E299',
          'brand-light': '#d4fae8',
          'brand-deep': '#0fa76e',
        },
        fontFamily: {
          sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
        },
      }
    }
  }
</script>

<style>
  *, *::before, *::after { box-sizing: border-box; }
  html { scroll-behavior: smooth; }
  body { font-family: 'Inter', sans-serif; color: #0d0d0d; background: #ffffff; }

  .material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    vertical-align: middle;
    display: inline-flex;
  }

  /* Shadows */
  .shadow-subtle  { box-shadow: rgba(0,0,0,0.03) 0px 2px 4px; }
  .shadow-btn     { box-shadow: rgba(0,0,0,0.06) 0px 1px 2px; }

  /* Chat bubbles */
  .bubble-bot  { background:#fafafa; border-radius:1rem 1rem 1rem 0.2rem; }
  .bubble-user { background:#0d0d0d; color:#fff; border-radius:1rem 1rem 0.2rem 1rem; }

  /* FAQ */
  .faq-answer { max-height:0; overflow:hidden; transition:max-height .35s ease; }
  .faq-answer.open { max-height:220px; }
  .faq-icon { transition:transform .3s ease; display:inline-block; }
  .faq-item.open .faq-icon { transform:rotate(45deg); }

  /* Reveal on scroll */
  .reveal { opacity:0; transform:translateY(20px); transition:opacity .55s ease, transform .55s ease; }
  .reveal.visible { opacity:1; transform:none; }

  /* Nav drawer */
  #nav-drawer { transform:translateX(100%); transition:transform .3s ease; }
  #nav-drawer.open { transform:translateX(0); }

  /* Industries card hover */
  .ind-card { transition: box-shadow .2s ease, transform .2s ease, border-color .2s ease; }
  .ind-card:hover { border-color:rgba(0,0,0,0.12); transform:translateY(-2px); box-shadow:rgba(0,0,0,0.04) 0px 8px 24px; }
</style>
</head>
<body>

<!-- ════════════════════════════════════
     NAV
════════════════════════════════════ -->
<nav class="fixed top-0 inset-x-0 z-50 bg-white/80 backdrop-blur-xl border-b border-[rgba(0,0,0,0.05)]">
  <div class="max-w-[1200px] mx-auto px-6 lg:px-8 flex items-center justify-between h-16">

    <a href="{{ url('/') }}" class="font-semibold text-lg text-[#0d0d0d] tracking-tight">ChatBot Nepal</a>

    <div class="hidden md:flex items-center gap-8 text-sm font-medium text-[#333333]">
      <a href="#about"   class="hover:text-brand transition-colors">About</a>
      <a href="#faq"     class="hover:text-brand transition-colors">FAQ</a>
      <a href="#contact" class="hover:text-brand transition-colors">Contact</a>
    </div>

    <div class="hidden md:flex items-center gap-3">
      <a href="{{ route('login') }}"
         class="text-sm font-medium text-[#0d0d0d] hover:text-brand transition-colors px-4 py-2">
        Login
      </a>
      <a href="https://wa.me/9779811144402?text=Namaste%21%20Chatbot%20ko%20bare%20ma%20kura%20garnu%20tha%20%F0%9F%99%8F"
         target="_blank" rel="noopener"
         class="inline-flex items-center bg-[#0d0d0d] hover:opacity-90 text-white text-sm font-medium px-5 py-2 rounded-[9999px] transition-opacity shadow-btn">
        Get Free Demo
      </a>
    </div>

    <button id="hamburger" class="md:hidden p-2 rounded-lg hover:bg-[#fafafa] transition-colors" aria-label="Open menu">
      <span class="material-symbols-outlined text-[#0d0d0d]">menu</span>
    </button>
  </div>
</nav>

<!-- Mobile drawer -->
<div id="drawer-overlay" class="fixed inset-0 z-40 bg-black/40 hidden"></div>
<div id="nav-drawer" class="fixed top-0 right-0 bottom-0 w-72 z-50 bg-white border-l border-[rgba(0,0,0,0.05)] flex flex-col">
  <div class="flex items-center justify-between px-5 py-4 border-b border-[rgba(0,0,0,0.05)]">
    <span class="font-semibold text-[#0d0d0d]">ChatBot Nepal</span>
    <button id="close-drawer" class="p-2 rounded-lg hover:bg-[#fafafa]">
      <span class="material-symbols-outlined text-[#666666]">close</span>
    </button>
  </div>
  <div class="flex flex-col gap-1 p-4 text-sm font-medium">
    <a href="#about"   class="drawer-link px-4 py-3 rounded-[9999px] text-[#333333] hover:bg-[#fafafa] hover:text-brand transition-colors">About</a>
    <a href="#faq"     class="drawer-link px-4 py-3 rounded-[9999px] text-[#333333] hover:bg-[#fafafa] hover:text-brand transition-colors">FAQ</a>
    <a href="#contact" class="drawer-link px-4 py-3 rounded-[9999px] text-[#333333] hover:bg-[#fafafa] hover:text-brand transition-colors">Contact</a>
  </div>
  <div class="mt-auto p-5 flex flex-col gap-3">
    <a href="{{ route('login') }}"
       class="block text-center bg-white text-[#0d0d0d] font-medium py-3 rounded-[9999px] border border-[rgba(0,0,0,0.08)] hover:opacity-90 transition-opacity">
      Login
    </a>
    <a href="https://wa.me/9779811144402?text=Namaste%21%20Chatbot%20ko%20bare%20ma%20kura%20garnu%20tha%20%F0%9F%99%8F"
       target="_blank" rel="noopener"
       class="block text-center bg-[#0d0d0d] text-white font-medium py-3 rounded-[9999px] hover:opacity-90 transition-opacity">
      Get Free Demo
    </a>
  </div>
</div>

<!-- ════════════════════════════════════
     HERO
════════════════════════════════════ -->
<section class="relative pt-28 pb-16 md:pt-36 md:pb-24 overflow-hidden" id="about">
  <!-- Atmospheric gradient -->
  <div class="absolute inset-0 bg-gradient-to-b from-brand-light/50 via-white to-white pointer-events-none"></div>

  <div class="relative max-w-[1200px] mx-auto px-6 lg:px-8">
    <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-16">

      <!-- Left -->
      <div class="flex-1 lg:max-w-[58%] reveal">
        <span class="inline-flex items-center bg-[#0d0d0d] text-white text-[13px] font-medium px-4 py-2 rounded-[9999px] mb-6">
          Built in Nepal · Serving Nepali SMEs
        </span>

        <h1 class="font-semibold text-4xl md:text-[64px] text-[#0d0d0d] leading-[1.15] tracking-[-0.02em] mb-5">
          Your Website Should Answer Customers — Automatically
        </h1>

        <p class="text-lg text-[#666666] leading-relaxed mb-8 max-w-lg">
          Your website answers customers instantly — in Nepali, Hindi &amp; English — 24/7.<br>
          We build it, manage it, update it. You just get more sales.
        </p>

        <div class="flex flex-col sm:flex-row items-center gap-4 mb-5">
          <a href="https://wa.me/9779811144402?text=Namaste%21%20Chatbot%20ko%20bare%20ma%20kura%20garnu%20tha%20%F0%9F%99%8F"
             target="_blank" rel="noopener"
             class="inline-flex items-center justify-center gap-2 bg-[#0d0d0d] hover:opacity-90 text-white font-medium py-4 px-8 rounded-[9999px] transition-opacity text-base shadow-btn">
            Book Your Free Demo &rarr;
          </a>
          <a href="#how-it-works"
             class="text-brand-deep font-medium text-sm hover:underline transition-colors">
            See How It Works &rarr;
          </a>
        </div>

        <p class="text-sm text-[#888888]">Setup in 24 hours &nbsp;&middot;&nbsp; No coding required &nbsp;&middot;&nbsp; Cancel anytime</p>
        <div class="mt-4 inline-flex items-center gap-2 bg-white border border-[rgba(0,0,0,0.05)] rounded-[9999px] px-4 py-2 shadow-subtle">
          <span class="material-symbols-outlined text-brand-deep" style="font-size:18px">location_on</span>
          <span class="text-sm font-medium text-[#0d0d0d]">Nepal Based</span>
          <span class="text-[#e5e5e5] mx-1">·</span>
          <span class="text-sm text-[#666666]">Local Support</span>
        </div>
      </div>

      <!-- Right — Chat mockup -->
      <div class="w-full max-w-sm mx-auto lg:mx-0 lg:flex-1 reveal" style="transition-delay:.15s">
        <div class="bg-white rounded-[24px] overflow-hidden border border-[rgba(0,0,0,0.05)] shadow-subtle lg:[transform:rotate(2deg)]">
          <!-- Header -->
          <div class="bg-[#0d0d0d] px-4 py-3 flex items-center gap-3">
            <div class="w-9 h-9 bg-white/10 rounded-full flex items-center justify-center flex-shrink-0">
              <span class="material-symbols-outlined text-white" style="font-size:18px">support_agent</span>
            </div>
            <div>
              <p class="text-white font-medium text-sm">Everest Coaching Center</p>
              <div class="flex items-center gap-1.5 mt-0.5">
                <span class="w-1.5 h-1.5 bg-brand rounded-full"></span>
                <span class="text-white/60 text-xs">Online now</span>
              </div>
            </div>
          </div>

          <!-- Messages -->
          <div class="bg-white p-4 flex flex-col gap-3" style="min-height:280px">
            <div class="flex gap-2 items-end">
              <div class="w-6 h-6 bg-[#0d0d0d] rounded-full flex-shrink-0 flex items-center justify-center">
                <span class="material-symbols-outlined text-white" style="font-size:13px">smart_toy</span>
              </div>
              <p class="bubble-bot px-3.5 py-2.5 text-sm text-[#0d0d0d] max-w-[80%]">
                Namaste! Everest Coaching Center ma swagat cha. Ke help chahiyo? 🙏
              </p>
            </div>

            <div class="flex justify-end">
              <p class="bubble-user px-3.5 py-2.5 text-sm max-w-[80%]">
                Morning batch ko fee kati ho?
              </p>
            </div>

            <div class="flex gap-2 items-end">
              <div class="w-6 h-6 bg-[#0d0d0d] rounded-full flex-shrink-0 flex items-center justify-center">
                <span class="material-symbols-outlined text-white" style="font-size:13px">smart_toy</span>
              </div>
              <p class="bubble-bot px-3.5 py-2.5 text-sm text-[#0d0d0d] max-w-[80%]">
                Monthly fee <strong>Rs. 3,500</strong> ho. Admission Rs. 1,000 one-time. Ahile open cha! 🎉
              </p>
            </div>

            <div class="flex justify-end">
              <p class="bubble-user px-3.5 py-2.5 text-sm max-w-[80%]">IELTS course ni cha?</p>
            </div>

            <div class="flex gap-2 items-end">
              <div class="w-6 h-6 bg-[#0d0d0d] rounded-full flex-shrink-0 flex items-center justify-center">
                <span class="material-symbols-outlined text-white" style="font-size:13px">smart_toy</span>
              </div>
              <p class="bubble-bot px-3.5 py-2.5 text-sm text-[#0d0d0d] max-w-[80%]">
                Cha! IELTS preparation course <strong>Rs. 8,000</strong>/month. Class Mon-Fri 7–9 AM. Free trial class available! 📚
              </p>
            </div>
          </div>

          <!-- Input -->
          <div class="bg-white border-t border-[rgba(0,0,0,0.05)] px-4 py-3 flex gap-2">
            <input type="text" placeholder="Type your message..." disabled
              class="flex-1 bg-[#fafafa] rounded-[9999px] px-4 py-2 text-sm text-[#666666] outline-none border border-[rgba(0,0,0,0.05)]">
            <button class="bg-[#0d0d0d] text-white p-2 rounded-full flex-shrink-0 hover:opacity-90 transition-opacity">
              <span class="material-symbols-outlined" style="font-size:18px">send</span>
            </button>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ════════════════════════════════════
     PROBLEM
════════════════════════════════════ -->
<section class="bg-white py-16 md:py-24 border-t border-[rgba(0,0,0,0.05)]">
  <div class="max-w-[1200px] mx-auto px-6 lg:px-8">
    <div class="text-center mb-12 reveal">
      <h2 class="font-semibold text-[40px] text-[#0d0d0d] leading-[1.10] tracking-[-0.02em] mb-3">You're Losing Customers Right Now</h2>
      <p class="text-[#666666] text-lg">Here's what happens every day on your website</p>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
      <div class="bg-white rounded-[16px] border border-[rgba(0,0,0,0.05)] p-8 reveal shadow-subtle hover:border-[rgba(0,0,0,0.08)] transition-colors">
        <div class="w-12 h-12 bg-[#fafafa] rounded-full flex items-center justify-center mb-5">
          <span class="material-symbols-outlined text-[#0d0d0d]" style="font-size:24px">schedule</span>
        </div>
        <h3 class="font-semibold text-lg text-[#0d0d0d] mb-2 tracking-[-0.01em]">Late-Night Queries</h3>
        <p class="text-[#666666] text-sm leading-relaxed">Customers message at 10 PM. Nobody replies until morning. They go to your competitor.</p>
      </div>

      <div class="bg-white rounded-[16px] border border-[rgba(0,0,0,0.05)] p-8 reveal shadow-subtle hover:border-[rgba(0,0,0,0.08)] transition-colors" style="transition-delay:.1s">
        <div class="w-12 h-12 bg-[#fafafa] rounded-full flex items-center justify-center mb-5">
          <span class="material-symbols-outlined text-[#0d0d0d]" style="font-size:24px">repeat</span>
        </div>
        <h3 class="font-semibold text-lg text-[#0d0d0d] mb-2 tracking-[-0.01em]">Repetitive Questions</h3>
        <p class="text-[#666666] text-sm leading-relaxed">Your staff wastes hours answering the same 20 questions. "Kati parcha?" "Kata ho?" every single day.</p>
      </div>

      <div class="bg-white rounded-[16px] border border-[rgba(0,0,0,0.05)] p-8 reveal shadow-subtle hover:border-[rgba(0,0,0,0.08)] transition-colors" style="transition-delay:.2s">
        <div class="w-12 h-12 bg-[#fafafa] rounded-full flex items-center justify-center mb-5">
          <span class="material-symbols-outlined text-[#0d0d0d]" style="font-size:24px">exit_to_app</span>
        </div>
        <h3 class="font-semibold text-lg text-[#0d0d0d] mb-2 tracking-[-0.01em]">Visitors Leaving</h3>
        <p class="text-[#666666] text-sm leading-relaxed">Without instant answers, visitors browse and leave. You paid for that traffic — and lost them.</p>
      </div>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════
     SOLUTION
════════════════════════════════════ -->
<section class="bg-white py-16 md:py-24 border-t border-[rgba(0,0,0,0.05)]">
  <div class="max-w-[1200px] mx-auto px-6 lg:px-8">
    <div class="text-center mb-14 reveal">
      <h2 class="font-semibold text-[40px] text-[#0d0d0d] leading-[1.10] tracking-[-0.02em] mb-3">Here's What Your Chatbot Does For You</h2>
    </div>

    <div class="grid md:grid-cols-3 gap-10 md:gap-16">
      <div class="reveal">
        <div class="font-semibold text-[7rem] leading-none text-[rgba(0,0,0,0.04)] select-none mb-[-2rem]">01</div>
        <div class="relative z-10">
          <h3 class="font-semibold text-xl text-[#0d0d0d] mb-3 tracking-[-0.01em]">Answers Customer Queries Instantly</h3>
          <p class="text-[#666666] leading-relaxed">No more "We'll get back to you." Your chatbot knows your services, pricing, FAQs and answers in seconds.</p>
        </div>
      </div>

      <div class="reveal" style="transition-delay:.1s">
        <div class="font-semibold text-[7rem] leading-none text-[rgba(0,0,0,0.04)] select-none mb-[-2rem]">02</div>
        <div class="relative z-10">
          <h3 class="font-semibold text-xl text-[#0d0d0d] mb-3 tracking-[-0.01em]">Captures Leads While You Sleep</h3>
          <p class="text-[#666666] leading-relaxed">Every midnight visitor becomes a potential customer. Chatbot collects their name, number, and what they need.</p>
        </div>
      </div>

      <div class="reveal" style="transition-delay:.2s">
        <div class="font-semibold text-[7rem] leading-none text-[rgba(0,0,0,0.04)] select-none mb-[-2rem]">03</div>
        <div class="relative z-10">
          <h3 class="font-semibold text-xl text-[#0d0d0d] mb-3 tracking-[-0.01em]">Speaks Your Customer's Language</h3>
          <p class="text-[#666666] leading-relaxed">Auto-detects Nepali, Hindi, or English. Customers type in their language, chatbot replies in the same.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════
     HOW IT WORKS
════════════════════════════════════ -->
<section class="bg-white py-16 md:py-24 border-t border-[rgba(0,0,0,0.05)]" id="how-it-works">
  <div class="max-w-[1200px] mx-auto px-6 lg:px-8">
    <div class="text-center mb-14 reveal">
      <h2 class="font-semibold text-[40px] text-[#0d0d0d] leading-[1.10] tracking-[-0.02em] mb-3">Live in 24 Hours</h2>
      <p class="text-[#666666] text-lg">We don't just give you a tool, we build the system for your business.</p>
    </div>

    <!-- Desktop timeline -->
    <div class="hidden md:grid grid-cols-4 gap-8 relative">
      <div class="absolute top-10 left-[12.5%] right-[12.5%] border-t-2 border-dashed border-[rgba(0,0,0,0.08)] z-0"></div>

      <div class="flex flex-col items-center text-center relative z-10 reveal">
        <div class="w-20 h-20 bg-[#fafafa] border border-[rgba(0,0,0,0.05)] rounded-full flex items-center justify-center mb-5 shadow-subtle">
          <span class="material-symbols-outlined text-[#0d0d0d]" style="font-size:28px">mic</span>
        </div>
        <h3 class="font-semibold text-sm text-[#0d0d0d] mb-2">We Interview You</h3>
        <p class="text-[#666666] text-sm">30 min call — we learn your business inside out</p>
      </div>

      <div class="flex flex-col items-center text-center relative z-10 reveal" style="transition-delay:.1s">
        <div class="w-20 h-20 bg-[#0d0d0d] rounded-full flex items-center justify-center mb-5 shadow-btn">
          <span class="material-symbols-outlined text-white" style="font-size:28px">psychology</span>
        </div>
        <h3 class="font-semibold text-sm text-[#0d0d0d] mb-2">Knowledge Build</h3>
        <p class="text-[#666666] text-sm">We build an AI knowledge base with your exact info</p>
      </div>

      <div class="flex flex-col items-center text-center relative z-10 reveal" style="transition-delay:.2s">
        <div class="w-20 h-20 bg-[#fafafa] border border-[rgba(0,0,0,0.05)] rounded-full flex items-center justify-center mb-5 shadow-subtle">
          <span class="material-symbols-outlined text-[#0d0d0d]" style="font-size:28px">code</span>
        </div>
        <h3 class="font-semibold text-sm text-[#0d0d0d] mb-2">One Line of Code</h3>
        <p class="text-[#666666] text-sm">Paste one line on your website. That's it.</p>
      </div>

      <div class="flex flex-col items-center text-center relative z-10 reveal" style="transition-delay:.3s">
        <div class="w-20 h-20 bg-[#fafafa] border border-[rgba(0,0,0,0.05)] rounded-full flex items-center justify-center mb-5 shadow-subtle">
          <span class="material-symbols-outlined text-[#0d0d0d]" style="font-size:28px">support_agent</span>
        </div>
        <h3 class="font-semibold text-sm text-[#0d0d0d] mb-2">24/7 Answers</h3>
        <p class="text-[#666666] text-sm">Customers get instant answers, you get more sales</p>
      </div>
    </div>

    <!-- Mobile timeline -->
    <div class="md:hidden flex flex-col">
      <div class="flex gap-5">
        <div class="flex flex-col items-center">
          <div class="w-14 h-14 bg-[#fafafa] border border-[rgba(0,0,0,0.05)] rounded-full flex items-center justify-center flex-shrink-0">
            <span class="material-symbols-outlined text-[#0d0d0d]" style="font-size:22px">mic</span>
          </div>
          <div class="w-0.5 flex-1 my-2 border-l-2 border-dashed border-[rgba(0,0,0,0.08)]"></div>
        </div>
        <div class="pt-2 pb-8">
          <h3 class="font-semibold text-[#0d0d0d] mb-1">We Interview You</h3>
          <p class="text-[#666666] text-sm">30 min call — we learn your business</p>
        </div>
      </div>

      <div class="flex gap-5">
        <div class="flex flex-col items-center">
          <div class="w-14 h-14 bg-[#0d0d0d] rounded-full flex items-center justify-center flex-shrink-0 shadow-btn">
            <span class="material-symbols-outlined text-white" style="font-size:22px">psychology</span>
          </div>
          <div class="w-0.5 flex-1 my-2 border-l-2 border-dashed border-[rgba(0,0,0,0.08)]"></div>
        </div>
        <div class="pt-2 pb-8">
          <h3 class="font-semibold text-[#0d0d0d] mb-1">Knowledge Build</h3>
          <p class="text-[#666666] text-sm">We build AI knowledge base with your exact info</p>
        </div>
      </div>

      <div class="flex gap-5">
        <div class="flex flex-col items-center">
          <div class="w-14 h-14 bg-[#fafafa] border border-[rgba(0,0,0,0.05)] rounded-full flex items-center justify-center flex-shrink-0">
            <span class="material-symbols-outlined text-[#0d0d0d]" style="font-size:22px">code</span>
          </div>
          <div class="w-0.5 flex-1 my-2 border-l-2 border-dashed border-[rgba(0,0,0,0.08)]"></div>
        </div>
        <div class="pt-2 pb-8">
          <h3 class="font-semibold text-[#0d0d0d] mb-1">One Line of Code</h3>
          <p class="text-[#666666] text-sm">Paste one line on your website. Done.</p>
        </div>
      </div>

      <div class="flex gap-5">
        <div class="flex flex-col items-center">
          <div class="w-14 h-14 bg-[#fafafa] border border-[rgba(0,0,0,0.05)] rounded-full flex items-center justify-center flex-shrink-0">
            <span class="material-symbols-outlined text-[#0d0d0d]" style="font-size:22px">support_agent</span>
          </div>
        </div>
        <div class="pt-2">
          <h3 class="font-semibold text-[#0d0d0d] mb-1">24/7 Answers</h3>
          <p class="text-[#666666] text-sm">Customers get instant answers, you get more sales</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════
     LIVE DEMO
════════════════════════════════════ -->
<section class="bg-white py-16 md:py-24 border-t border-[rgba(0,0,0,0.05)]">
  <div class="max-w-4xl mx-auto px-6 lg:px-8">
    <div class="text-center mb-12 reveal">
      <h2 class="font-semibold text-[40px] text-[#0d0d0d] leading-[1.10] tracking-[-0.02em] mb-3">Try It Live — Right Now</h2>
      <p class="text-[#666666] text-lg">Real chatbot. Type anything below and see how it responds.</p>
      <div class="inline-flex items-center gap-2 mt-3 bg-brand-light text-brand-deep text-xs font-medium px-4 py-2 rounded-[9999px]">
        <span class="w-2 h-2 bg-brand-deep rounded-full animate-pulse inline-block"></span>
        Live demo — this is an actual AI chatbot, not a recording
      </div>
    </div>

    <!-- Desktop: browser mockup -->
    <div class="hidden sm:block bg-white rounded-[24px] border border-[rgba(0,0,0,0.05)] overflow-hidden reveal shadow-subtle">
      <!-- Chrome bar -->
      <div class="bg-[#fafafa] border-b border-[rgba(0,0,0,0.05)] px-4 py-3 flex items-center gap-3">
        <div class="flex gap-1.5">
          <span class="w-3 h-3 rounded-full bg-[#d45656] inline-block"></span>
          <span class="w-3 h-3 rounded-full bg-[#c37d0d] inline-block"></span>
          <span class="w-3 h-3 rounded-full bg-brand-deep inline-block"></span>
        </div>
        <div class="flex-1 bg-white rounded-[9999px] px-3 py-1.5 text-xs text-[#888888] font-mono border border-[rgba(0,0,0,0.05)]">
          everestcoaching.com.np
        </div>
      </div>

      <!-- Page body -->
      <div class="relative bg-white overflow-hidden" style="min-height:440px">
        <!-- Blurred fake page -->
        <div class="p-8 opacity-10 select-none pointer-events-none">
          <div class="h-5 bg-[#0d0d0d] rounded w-48 mb-4"></div>
          <div class="h-3 bg-[#666666] rounded w-72 mb-2"></div>
          <div class="h-3 bg-[#666666] rounded w-64 mb-2"></div>
          <div class="h-3 bg-[#666666] rounded w-56 mb-8"></div>
          <div class="flex gap-3">
            <div class="h-8 w-28 bg-[#0d0d0d] rounded-[9999px]"></div>
            <div class="h-8 w-24 bg-[#666666]/40 rounded-[9999px]"></div>
          </div>
        </div>

        <!-- Floating chat widget bottom-right -->
        <div class="absolute bottom-4 right-4 w-72 bg-white rounded-2xl overflow-hidden border border-[rgba(0,0,0,0.08)] shadow-subtle">
          <div class="bg-[#0d0d0d] px-3 py-2.5 flex items-center gap-2">
            <div class="w-7 h-7 bg-white/10 rounded-full flex items-center justify-center flex-shrink-0">
              <span class="material-symbols-outlined text-white" style="font-size:14px">smart_toy</span>
            </div>
            <div>
              <p class="text-white font-medium text-xs">Everest Coaching</p>
              <div class="flex items-center gap-1">
                <span class="w-1.5 h-1.5 bg-brand rounded-full inline-block"></span>
                <span class="text-white/50 text-[10px]">Online</span>
              </div>
            </div>
          </div>
          <div id="demo-messages-desktop" class="bg-white p-3 flex flex-col gap-2" style="max-height:260px;overflow-y:auto">
            <p class="bubble-bot px-3 py-2 text-xs text-[#0d0d0d] max-w-[90%]">Namaste! Hamro coaching center bare kei sodhnu cha? 🙏</p>
            <p class="text-center text-[10px] text-[#888888] italic">👆 Try typing something below!</p>
          </div>
          <div class="bg-white border-t border-[rgba(0,0,0,0.05)] px-3 py-2 flex gap-2">
            <input type="text" id="demo-input-desktop" placeholder="e.g. fee kati ho?" class="flex-1 bg-[#fafafa] rounded-[9999px] px-3 py-1.5 text-xs text-[#666666] outline-none border border-[rgba(0,0,0,0.05)] focus:border-brand transition-colors">
            <button onclick="demoSend('desktop')" class="bg-[#0d0d0d] text-white p-1.5 rounded-full hover:opacity-90 transition-opacity">
              <span class="material-symbols-outlined" style="font-size:14px">send</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile: standalone chat widget -->
    <div class="sm:hidden bg-white rounded-[24px] overflow-hidden border border-[rgba(0,0,0,0.05)] shadow-subtle reveal">
      <div class="bg-[#0d0d0d] px-4 py-3 flex items-center gap-3">
        <div class="w-8 h-8 bg-white/10 rounded-full flex items-center justify-center flex-shrink-0">
          <span class="material-symbols-outlined text-white" style="font-size:16px">smart_toy</span>
        </div>
        <div>
          <p class="text-white font-medium text-sm">Everest Coaching</p>
          <div class="flex items-center gap-1.5 mt-0.5">
            <span class="w-1.5 h-1.5 bg-brand rounded-full inline-block"></span>
            <span class="text-white/50 text-xs">Online now</span>
          </div>
        </div>
      </div>
      <div id="demo-messages-mobile" class="bg-white p-4 flex flex-col gap-3" style="max-height:320px;overflow-y:auto">
        <p class="bubble-bot px-3.5 py-2.5 text-sm text-[#0d0d0d] max-w-[90%]">Namaste! Hamro coaching center bare kei sodhnu cha? 🙏</p>
        <p class="text-center text-xs text-[#888888] italic">👆 Type a question below and try it!</p>
      </div>
      <div class="bg-white border-t border-[rgba(0,0,0,0.05)] px-4 py-3 flex gap-2">
        <input type="text" id="demo-input-mobile" placeholder="e.g. IELTS course cha?" class="flex-1 bg-[#fafafa] rounded-[9999px] px-3 py-2 text-sm text-[#666666] outline-none border border-[rgba(0,0,0,0.05)] focus:border-brand transition-colors">
        <button onclick="demoSend('mobile')" class="bg-[#0d0d0d] hover:opacity-90 text-white p-2 rounded-full transition-opacity">
          <span class="material-symbols-outlined" style="font-size:18px">send</span>
        </button>
      </div>
    </div>

    <div class="text-center mt-10 reveal">
      <p class="text-[#666666] mb-6">This is a real AI chatbot we built for a local coaching institute. Want one for your business?</p>
      <a href="https://wa.me/9779811144402?text=Namaste%21%20Chatbot%20ko%20bare%20ma%20kura%20garnu%20tha%20%F0%9F%99%8F"
         target="_blank" rel="noopener"
         class="inline-flex items-center gap-2 bg-[#0d0d0d] hover:opacity-90 text-white font-medium py-4 px-8 rounded-[9999px] transition-opacity shadow-btn">
        Get Your Free Demo
      </a>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════
     TESTIMONIALS
════════════════════════════════════ -->
<section class="bg-white py-16 md:py-20 border-t border-[rgba(0,0,0,0.05)]">
  <div class="max-w-5xl mx-auto px-6 lg:px-8">
    <div class="text-center mb-12 reveal">
      <h2 class="font-semibold text-[40px] text-[#0d0d0d] leading-[1.10] tracking-[-0.02em] mb-3">What Our Beta Clients Say</h2>
      <p class="text-[#666666]">Real feedback from our early partners across Nepal</p>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
      <!-- Testimonial 1 -->
      <div class="bg-white border border-[rgba(0,0,0,0.05)] rounded-[16px] p-6 flex flex-col gap-4 reveal shadow-subtle">
        <div class="flex gap-1 text-[#c37d0d]">
          <span class="material-symbols-outlined" style="font-size:18px;font-variation-settings:'FILL' 1">star</span>
          <span class="material-symbols-outlined" style="font-size:18px;font-variation-settings:'FILL' 1">star</span>
          <span class="material-symbols-outlined" style="font-size:18px;font-variation-settings:'FILL' 1">star</span>
          <span class="material-symbols-outlined" style="font-size:18px;font-variation-settings:'FILL' 1">star</span>
          <span class="material-symbols-outlined" style="font-size:18px;font-variation-settings:'FILL' 1">star</span>
        </div>
        <p class="text-[#666666] text-sm leading-relaxed italic">
          "Ahile haami raat ko leads ni paunchha. Pahile bhane website ma ko gayo ko tha thaha hudaina. Chatbot le sab capture garcha!"
        </p>
        <div class="flex items-center gap-3 mt-auto">
          <div class="w-10 h-10 bg-brand-light rounded-full flex items-center justify-center flex-shrink-0">
            <span class="font-semibold text-brand-deep text-sm">R</span>
          </div>
          <div>
            <p class="font-medium text-[#0d0d0d] text-sm">Ramesh Shrestha</p>
            <p class="text-[#888888] text-xs">Everest Coaching Center, Lalitpur</p>
          </div>
        </div>
      </div>

      <!-- Testimonial 2 -->
      <div class="bg-white border border-[rgba(0,0,0,0.05)] rounded-[16px] p-6 flex flex-col gap-4 reveal shadow-subtle" style="transition-delay:.1s">
        <div class="flex gap-1 text-[#c37d0d]">
          <span class="material-symbols-outlined" style="font-size:18px;font-variation-settings:'FILL' 1">star</span>
          <span class="material-symbols-outlined" style="font-size:18px;font-variation-settings:'FILL' 1">star</span>
          <span class="material-symbols-outlined" style="font-size:18px;font-variation-settings:'FILL' 1">star</span>
          <span class="material-symbols-outlined" style="font-size:18px;font-variation-settings:'FILL' 1">star</span>
          <span class="material-symbols-outlined" style="font-size:18px;font-variation-settings:'FILL' 1">star</span>
        </div>
        <p class="text-[#666666] text-sm leading-relaxed italic">
          "Setup was done in less than 24 hours! Devbarat took our clinic schedule and made the bot answer appointment questions in both Nepali and English. Excellent service."
        </p>
        <div class="flex items-center gap-3 mt-auto">
          <div class="w-10 h-10 bg-brand-light rounded-full flex items-center justify-center flex-shrink-0">
            <span class="font-semibold text-brand-deep text-sm">S</span>
          </div>
          <div>
            <p class="font-medium text-[#0d0d0d] text-sm">Dr. Sunita Karki</p>
            <p class="text-[#888888] text-xs">Suryodaya Clinic, Bhaktapur</p>
          </div>
        </div>
      </div>

      <!-- Testimonial 3 -->
      <div class="bg-white border border-[rgba(0,0,0,0.05)] rounded-[16px] p-6 flex flex-col gap-4 reveal shadow-subtle" style="transition-delay:.2s">
        <div class="flex gap-1 text-[#c37d0d]">
          <span class="material-symbols-outlined" style="font-size:18px;font-variation-settings:'FILL' 1">star</span>
          <span class="material-symbols-outlined" style="font-size:18px;font-variation-settings:'FILL' 1">star</span>
          <span class="material-symbols-outlined" style="font-size:18px;font-variation-settings:'FILL' 1">star</span>
          <span class="material-symbols-outlined" style="font-size:18px;font-variation-settings:'FILL' 1">star</span>
          <span class="material-symbols-outlined" style="font-size:18px;font-variation-settings:'FILL' 1">star</span>
        </div>
        <p class="text-[#666666] text-sm leading-relaxed italic">
          "Our hotel booking queries reduced staff workload by half. Guests get instant answers about room availability, rates, and facilities — even at midnight."
        </p>
        <div class="flex items-center gap-3 mt-auto">
          <div class="w-10 h-10 bg-brand-light rounded-full flex items-center justify-center flex-shrink-0">
            <span class="font-semibold text-brand-deep text-sm">B</span>
          </div>
          <div>
            <p class="font-medium text-[#0d0d0d] text-sm">Bikash Tamang</p>
            <p class="text-[#888888] text-xs">Mountain View Guest House, Pokhara</p>
          </div>
        </div>
      </div>
    </div>

    <p class="text-center text-xs text-[#888888] mt-8">* Beta client testimonials. Full case studies available on request.</p>
  </div>
</section>

<!-- ════════════════════════════════════
     INDUSTRIES
════════════════════════════════════ -->
<section class="bg-white py-16 md:py-24 border-t border-[rgba(0,0,0,0.05)]">
  <div class="max-w-[1200px] mx-auto px-6 lg:px-8">
    <div class="text-center mb-12 reveal">
      <h2 class="font-semibold text-[40px] text-[#0d0d0d] leading-[1.10] tracking-[-0.02em] mb-3">Built for Businesses Like Yours</h2>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 md:gap-6">
      <div class="ind-card bg-white border border-[rgba(0,0,0,0.05)] rounded-[16px] p-6 text-center reveal">
        <div class="w-12 h-12 bg-[#fafafa] rounded-full flex items-center justify-center mx-auto mb-4">
          <span class="material-symbols-outlined text-[#0d0d0d]">school</span>
        </div>
        <h3 class="font-medium text-[#0d0d0d] text-sm mb-1">Coaching Centers</h3>
        <p class="text-[#888888] text-xs leading-relaxed">Course fees, schedules, admission queries</p>
      </div>

      <div class="ind-card bg-white border border-[rgba(0,0,0,0.05)] rounded-[16px] p-6 text-center reveal" style="transition-delay:.05s">
        <div class="w-12 h-12 bg-[#fafafa] rounded-full flex items-center justify-center mx-auto mb-4">
          <span class="material-symbols-outlined text-[#0d0d0d]">hotel</span>
        </div>
        <h3 class="font-medium text-[#0d0d0d] text-sm mb-1">Hotels &amp; Restaurants</h3>
        <p class="text-[#888888] text-xs leading-relaxed">Booking, menu, room availability</p>
      </div>

      <div class="ind-card bg-white border border-[rgba(0,0,0,0.05)] rounded-[16px] p-6 text-center reveal" style="transition-delay:.1s">
        <div class="w-12 h-12 bg-[#fafafa] rounded-full flex items-center justify-center mx-auto mb-4">
          <span class="material-symbols-outlined text-[#0d0d0d]">local_hospital</span>
        </div>
        <h3 class="font-medium text-[#0d0d0d] text-sm mb-1">Clinics</h3>
        <p class="text-[#888888] text-xs leading-relaxed">Appointments, doctor schedules, services</p>
      </div>

      <div class="ind-card bg-white border border-[rgba(0,0,0,0.05)] rounded-[16px] p-6 text-center reveal" style="transition-delay:.15s">
        <div class="w-12 h-12 bg-[#fafafa] rounded-full flex items-center justify-center mx-auto mb-4">
          <span class="material-symbols-outlined text-[#0d0d0d]">storefront</span>
        </div>
        <h3 class="font-medium text-[#0d0d0d] text-sm mb-1">Retail</h3>
        <p class="text-[#888888] text-xs leading-relaxed">Product info, pricing, store hours</p>
      </div>

      <div class="ind-card bg-white border border-[rgba(0,0,0,0.05)] rounded-[16px] p-6 text-center reveal" style="transition-delay:.2s">
        <div class="w-12 h-12 bg-[#fafafa] rounded-full flex items-center justify-center mx-auto mb-4">
          <span class="material-symbols-outlined text-[#0d0d0d]">campaign</span>
        </div>
        <h3 class="font-medium text-[#0d0d0d] text-sm mb-1">Marketing Agencies</h3>
        <p class="text-[#888888] text-xs leading-relaxed">Service details, portfolio, booking</p>
      </div>

      <div class="ind-card bg-white border border-[rgba(0,0,0,0.05)] rounded-[16px] p-6 text-center reveal" style="transition-delay:.25s">
        <div class="w-12 h-12 bg-[#fafafa] rounded-full flex items-center justify-center mx-auto mb-4">
          <span class="material-symbols-outlined text-[#0d0d0d]">real_estate_agent</span>
        </div>
        <h3 class="font-medium text-[#0d0d0d] text-sm mb-1">Real Estate</h3>
        <p class="text-[#888888] text-xs leading-relaxed">Property details, pricing, visits</p>
      </div>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════
     PULL QUOTE
════════════════════════════════════ -->
<section class="bg-white py-10 md:py-14 border-t border-[rgba(0,0,0,0.05)]">
  <div class="max-w-2xl mx-auto px-6 lg:px-8 text-center reveal">
    <blockquote class="relative">
      <span class="absolute -top-4 left-1/2 -translate-x-1/2 text-7xl text-brand/10 font-serif leading-none select-none">&ldquo;</span>
      <p class="relative z-10 text-xl md:text-2xl font-semibold text-[#0d0d0d] italic leading-relaxed px-4">
        "That's exactly why our monthly fee exists — we maintain everything."
      </p>
      <footer class="mt-4 text-sm text-[#666666]">— ChatBot Nepal's commitment to every client</footer>
    </blockquote>
  </div>
</section>

<!-- ════════════════════════════════════
     SOCIAL PROOF
════════════════════════════════════ -->
<section class="bg-white py-16 md:py-20 border-t border-[rgba(0,0,0,0.05)]">
  <div class="max-w-4xl mx-auto px-6 lg:px-8 text-center">
    <div class="reveal mb-10">
      <h2 class="font-semibold text-3xl text-[#0d0d0d] tracking-[-0.02em] mb-4">Be One of Our First 10 Clients</h2>
      <p class="text-[#666666] text-lg leading-relaxed max-w-2xl mx-auto">
        We are currently in beta and looking for 10 visionary Nepali businesses to be our early partners. You'll get VIP support, the lowest rate locked in permanently, and a chatbot built with extra care.
      </p>
    </div>

    <div class="grid md:grid-cols-3 gap-5">
      <div class="bg-white border border-[rgba(0,0,0,0.05)] rounded-[16px] p-6 flex flex-col items-center gap-3 shadow-subtle reveal">
        <div class="w-12 h-12 bg-[#fafafa] rounded-full flex items-center justify-center">
          <span class="material-symbols-outlined text-[#0d0d0d]">priority_high</span>
        </div>
        <h3 class="font-medium text-[#0d0d0d] text-sm">Priority 24/7 Support</h3>
        <p class="text-[#888888] text-xs">Direct access to our team, always</p>
      </div>

      <div class="bg-white border border-[rgba(0,0,0,0.05)] rounded-[16px] p-6 flex flex-col items-center gap-3 shadow-subtle reveal" style="transition-delay:.1s">
        <div class="w-12 h-12 bg-[#fafafa] rounded-full flex items-center justify-center">
          <span class="material-symbols-outlined text-[#0d0d0d]">monitoring</span>
        </div>
        <h3 class="font-medium text-[#0d0d0d] text-sm">Weekly Performance Audits</h3>
        <p class="text-[#888888] text-xs">We review your chatbot every week</p>
      </div>

      <div class="bg-white border border-[rgba(0,0,0,0.05)] rounded-[16px] p-6 flex flex-col items-center gap-3 shadow-subtle reveal" style="transition-delay:.2s">
        <div class="w-12 h-12 bg-[#fafafa] rounded-full flex items-center justify-center">
          <span class="material-symbols-outlined text-[#0d0d0d]">location_on</span>
        </div>
        <h3 class="font-medium text-[#0d0d0d] text-sm">Nepal-Based Team</h3>
        <p class="text-[#888888] text-xs">We speak your language, literally</p>
      </div>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════
     FAQ
════════════════════════════════════ -->
<section class="bg-white py-16 md:py-24 border-t border-[rgba(0,0,0,0.05)]" id="faq">
  <div class="max-w-3xl mx-auto px-6 lg:px-8">
    <div class="text-center mb-12 reveal">
      <h2 class="font-semibold text-[40px] text-[#0d0d0d] leading-[1.10] tracking-[-0.02em] mb-3">Common Questions</h2>
    </div>

    <div class="flex flex-col gap-2">
      <div class="faq-item bg-[#fafafa] rounded-[16px] overflow-hidden reveal">
        <button class="faq-btn w-full flex items-center justify-between px-5 py-4 text-left gap-4">
          <span class="font-medium text-[#0d0d0d] text-sm md:text-base">How long does setup take?</span>
          <span class="material-symbols-outlined faq-icon text-[#888888] flex-shrink-0">add</span>
        </button>
        <div class="faq-answer">
          <p class="px-5 pb-5 text-[#666666] text-sm leading-relaxed">24 hours from our interview call. We collect your info, build the knowledge base, and deploy. You paste one line of code.</p>
        </div>
      </div>

      <div class="faq-item bg-[#fafafa] rounded-[16px] overflow-hidden reveal">
        <button class="faq-btn w-full flex items-center justify-between px-5 py-4 text-left gap-4">
          <span class="font-medium text-[#0d0d0d] text-sm md:text-base">Does it understand Nepali in Roman letters?</span>
          <span class="material-symbols-outlined faq-icon text-[#888888] flex-shrink-0">add</span>
        </button>
        <div class="faq-answer">
          <p class="px-5 pb-5 text-[#666666] text-sm leading-relaxed">Yes. Customers can type "fee kati ho?" in romanized Nepali. The chatbot understands and replies perfectly.</p>
        </div>
      </div>

      <div class="faq-item bg-[#fafafa] rounded-[16px] overflow-hidden reveal">
        <button class="faq-btn w-full flex items-center justify-between px-5 py-4 text-left gap-4">
          <span class="font-medium text-[#0d0d0d] text-sm md:text-base">Can I track leads and queries?</span>
          <span class="material-symbols-outlined faq-icon text-[#888888] flex-shrink-0">add</span>
        </button>
        <div class="faq-answer">
          <p class="px-5 pb-5 text-[#666666] text-sm leading-relaxed">Yes. You get a dashboard showing every visitor query, every lead captured, and every contact detail collected.</p>
        </div>
      </div>

      <div class="faq-item bg-[#fafafa] rounded-[16px] overflow-hidden reveal">
        <button class="faq-btn w-full flex items-center justify-between px-5 py-4 text-left gap-4">
          <span class="font-medium text-[#0d0d0d] text-sm md:text-base">Is there a contract?</span>
          <span class="material-symbols-outlined faq-icon text-[#888888] flex-shrink-0">add</span>
        </button>
        <div class="faq-answer">
          <p class="px-5 pb-5 text-[#666666] text-sm leading-relaxed">No. Month-to-month. Cancel anytime. No lock-in.</p>
        </div>
      </div>

      <div class="faq-item bg-[#fafafa] rounded-[16px] overflow-hidden reveal">
        <button class="faq-btn w-full flex items-center justify-between px-5 py-4 text-left gap-4">
          <span class="font-medium text-[#0d0d0d] text-sm md:text-base">What if my pricing or services change?</span>
          <span class="material-symbols-outlined faq-icon text-[#888888] flex-shrink-0">add</span>
        </button>
        <div class="faq-answer">
          <p class="px-5 pb-5 text-[#666666] text-sm leading-relaxed">Message us. We update your chatbot's knowledge base same day. That's exactly why our monthly fee exists — we maintain everything.</p>
        </div>
      </div>

      <div class="faq-item bg-[#fafafa] rounded-[16px] overflow-hidden reveal">
        <button class="faq-btn w-full flex items-center justify-between px-5 py-4 text-left gap-4">
          <span class="font-medium text-[#0d0d0d] text-sm md:text-base">How do I pay? Do you accept eSewa or Khalti?</span>
          <span class="material-symbols-outlined faq-icon text-[#888888] flex-shrink-0">add</span>
        </button>
        <div class="faq-answer">
          <p class="px-5 pb-5 text-[#666666] text-sm leading-relaxed">We accept <strong>eSewa</strong>, <strong>Khalti</strong>, and direct bank transfer. No international card needed. After your demo call we'll send you the payment details — setup begins immediately after payment.</p>
        </div>
      </div>

      <div class="faq-item bg-[#fafafa] rounded-[16px] overflow-hidden reveal">
        <button class="faq-btn w-full flex items-center justify-between px-5 py-4 text-left gap-4">
          <span class="font-medium text-[#0d0d0d] text-sm md:text-base">Will it slow my website?</span>
          <span class="material-symbols-outlined faq-icon text-[#888888] flex-shrink-0">add</span>
        </button>
        <div class="faq-answer">
          <p class="px-5 pb-5 text-[#666666] text-sm leading-relaxed">No. Single lightweight script, loads asynchronously. Zero impact on your site speed.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════
     FOUNDER
════════════════════════════════════ -->
<section class="bg-white py-16 md:py-20 border-t border-[rgba(0,0,0,0.05)]">
  <div class="max-w-2xl mx-auto px-6 lg:px-8 reveal">
    <div class="bg-white border border-[rgba(0,0,0,0.05)] rounded-[24px] p-8 md:p-12 text-center shadow-subtle">
      <img src="{{ asset('images/devbarat.jpg') }}" alt="Devbarat — Founder, ChatBot Nepal"
           class="w-28 h-28 rounded-full object-cover mx-auto mb-5 border-4 border-brand-light shadow-subtle">
      <h3 class="font-semibold text-xl text-[#0d0d0d] mb-1">Devbarat</h3>
      <p class="text-[#666666] text-sm mb-1">Founder &amp; Developer — ChatBot Nepal</p>
      <p class="text-[#888888] text-xs mb-6">iSoftro · Kathmandu, Nepal</p>
      <blockquote class="relative">
        <span class="absolute -top-3 left-0 text-6xl text-brand/10 font-serif leading-none select-none">&ldquo;</span>
        <p class="relative z-10 text-[#666666] text-sm md:text-base leading-relaxed italic px-4 text-left">

          मैले यो सेवा यसकारण बनाएँ किनभने मैले देखेँ कि नेपालका राम्रा व्यवसायहरू पनि केवल यसकारण ग्राहक गुमाइरहेका थिए किनभने उनीहरूको वेबसाइटले जवाफ दिँदैनथ्यो। म आफैं प्रत्येक च्याटबोट सेटअप गर्छु — मेरो नम्बर प्रत्यक्ष WhatsApp मा उपलब्ध छ।
        </p>
      </blockquote>
      <p class="text-xs text-[#888888] mt-4 mb-8">— Devbarat, PHP/Laravel Developer &nbsp;·&nbsp; iSoftro &nbsp;·&nbsp; Kathmandu, Nepal</p>
      <a href="https://wa.me/9779811144402?text=Namaste%21%20Chatbot%20ko%20bare%20ma%20kura%20garnu%20tha%20%F0%9F%99%8F"
         target="_blank" rel="noopener"
         class="inline-flex items-center gap-2 bg-[#0d0d0d] hover:opacity-90 text-white font-medium py-3.5 px-8 rounded-[9999px] transition-opacity shadow-btn">
        <span class="material-symbols-outlined" style="font-size:18px">chat</span>
        Message Devbarat Directly &rarr;
      </a>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════
     FINAL CTA
════════════════════════════════════ -->
<section class="bg-[#0d0d0d] py-16 md:py-24" id="contact">
  <div class="max-w-3xl mx-auto px-6 lg:px-8 text-center reveal">
    <h2 class="font-semibold text-3xl md:text-[40px] text-white leading-[1.10] tracking-[-0.02em] mb-4">
      Ready to Stop Losing Customers?
    </h2>
    <p class="text-white/60 text-lg mb-10 max-w-xl mx-auto">
      Join the 10 exclusive beta partners today and bring your website to life.
    </p>

    <div class="flex flex-col sm:flex-row gap-4 justify-center">
      <a href="https://wa.me/9779811144402?text=Namaste%21%20Chatbot%20ko%20bare%20ma%20kura%20garnu%20tha%20%F0%9F%99%8F"
         target="_blank" rel="noopener"
         class="inline-flex items-center justify-center gap-2 text-[#0d0d0d] font-medium py-4 px-8 rounded-[9999px] transition-opacity hover:opacity-90"
         style="background:#18E299">
        <span class="material-symbols-outlined" style="font-size:20px">chat</span>
        Talk to Our Agent Now
      </a>
      <a href="mailto:info@isoftro.com"
         class="inline-flex items-center justify-center gap-2 border border-white/20 text-white font-medium py-4 px-8 rounded-[9999px] hover:bg-white/10 transition-colors">
        <span class="material-symbols-outlined" style="font-size:20px">mail</span>
        Send Email
      </a>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════
     FOOTER
════════════════════════════════════ -->
<footer class="bg-white py-10 border-t border-[rgba(0,0,0,0.05)]">
  <div class="max-w-[1200px] mx-auto px-6 lg:px-8">
    <div class="flex flex-col md:flex-row items-center justify-between gap-5">
      <span class="font-semibold text-lg text-[#0d0d0d] tracking-tight">ChatBot Nepal</span>
      <div class="flex flex-wrap items-center justify-center gap-6 text-sm text-[#666666]">
        <a href="{{ route('privacy-policy') }}" class="hover:text-brand transition-colors">Privacy Policy</a>
        <a href="{{ route('terms') }}" class="hover:text-brand transition-colors">Terms of Service</a>
        <a href="mailto:info@isoftro.com" class="hover:text-brand transition-colors">Contact Support</a>
      </div>
    </div>
    <div class="border-t border-[rgba(0,0,0,0.05)] mt-6 pt-6 text-center">
      <p class="text-[#666666] text-sm">&copy; 2026 ChatBot Nepal. Kathmandu, Nepal</p>
      <p class="text-[#888888] text-xs mt-2">
        Built by <a href="https://isoftroerp.com/" target="_blank" rel="noopener" class="text-brand-deep font-medium hover:underline">ISoftroERP</a>
      </p>
    </div>
  </div>
</footer>

<script>
// ── FAQ accordion ──
document.querySelectorAll('.faq-btn').forEach(function(btn) {
  btn.addEventListener('click', function() {
    var item = btn.closest('.faq-item');
    var answer = item.querySelector('.faq-answer');
    var isOpen = item.classList.contains('open');
    document.querySelectorAll('.faq-item').forEach(function(i) {
      i.classList.remove('open');
      i.querySelector('.faq-answer').classList.remove('open');
    });
    if (!isOpen) {
      item.classList.add('open');
      answer.classList.add('open');
    }
  });
});

// ── Mobile nav ──
var hamburger    = document.getElementById('hamburger');
var drawer       = document.getElementById('nav-drawer');
var overlay      = document.getElementById('drawer-overlay');
var closeDrawer  = document.getElementById('close-drawer');

function openNav() {
  drawer.classList.add('open');
  overlay.classList.remove('hidden');
  document.body.style.overflow = 'hidden';
}
function closeNav() {
  drawer.classList.remove('open');
  overlay.classList.add('hidden');
  document.body.style.overflow = '';
}

hamburger.addEventListener('click', openNav);
closeDrawer.addEventListener('click', closeNav);
overlay.addEventListener('click', closeNav);
document.querySelectorAll('.drawer-link').forEach(function(link) {
  link.addEventListener('click', closeNav);
});

// ── Smooth scroll ──
document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
  anchor.addEventListener('click', function(e) {
    var target = document.querySelector(anchor.getAttribute('href'));
    if (target) {
      e.preventDefault();
      var top = target.getBoundingClientRect().top + window.scrollY - 70;
      window.scrollTo({ top: top, behavior: 'smooth' });
    }
  });
});

// ── Reveal on scroll ──
var revealObserver = new IntersectionObserver(function(entries) {
  entries.forEach(function(entry, i) {
    if (entry.isIntersecting) {
      setTimeout(function() {
        entry.target.classList.add('visible');
      }, i * 60);
      revealObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.08 });

document.querySelectorAll('.reveal').forEach(function(el) {
  revealObserver.observe(el);
});

</script>
<!-- ── Sticky mobile CTA bar ── -->
<div id="sticky-cta" class="md:hidden fixed bottom-0 inset-x-0 z-50 bg-[#0d0d0d] border-t border-[rgba(255,255,255,0.1)] px-4 py-3 translate-y-full transition-transform duration-300">
  <a href="https://wa.me/9779811144402?text=Namaste%21%20Chatbot%20ko%20bare%20ma%20kura%20garnu%20tha%20%F0%9F%99%8F"
     target="_blank" rel="noopener"
     class="flex items-center justify-center gap-2 text-[#0d0d0d] font-medium py-3.5 rounded-[9999px] text-base w-full hover:opacity-90 transition-opacity"
     style="background:#18E299">
    <span class="material-symbols-outlined" style="font-size:20px">chat</span>
    Get Free Demo &rarr;
  </a>
</div>

<!-- ChatBot Nepal Widget -->
<script src="https://chatbotnepal.isoftroerp.com/widget.js" data-token="iVmfZwgDohSpk1yiQNJMPWJSdiocHPbnTwImj8fIf0JNoHFgHui4tsoTU2lf7NOz" defer></script>
<!-- End ChatBot Nepal Widget -->

<script>
// ── Demo chatbot simulation ──
var demoReplies = [
  { pattern: /fee|kati|cost|price|paisa|rupee|rs\.|charge/i,
    reply: 'Monthly fee <strong>Rs. 3,500</strong> ho. Admission Rs. 1,000 one-time. Total pahilo mahina Rs. 4,500. Ahile open cha — join garnu huncha? 🎉' },
  { pattern: /ielts|english|language|course/i,
    reply: 'IELTS Preparation course cha! <strong>Rs. 8,000/month</strong>. Class Mon–Fri 7–9 AM. Free trial class available. Book garnu huncha? 📚' },
  { pattern: /batch|time|class|schedule|kati baje|suru|start/i,
    reply: 'Morning batch <strong>6:30 AM</strong>, daytime batch <strong>11:00 AM</strong>, evening batch <strong>4:00 PM</strong>. Kun batch convenient cha? 🕒' },
  { pattern: /admission|enroll|join|bharna|enter/i,
    reply: 'Admission process ekdam simple cha! Form fill garnu, Rs. 1,000 pay garnu, ani class suru. Aaunus na — aaja nai enroll garnus! 📝' },
  { pattern: /location|kata|address|kahan|where/i,
    reply: 'Hamro center Lalitpur, Pulchowk ma cha. Lalitpur Bus Park bata 5 min walk. Google Maps link: bit.ly/everest-coaching 📍' },
  { pattern: /contact|phone|call|number/i,
    reply: 'Hamlai call garnus: <strong>9801234567</strong>. WhatsApp ma ni available cha same number ma. 📞' },
  { pattern: /hello|hi|namaste|namaskar|hey/i,
    reply: 'Namaste! Everest Coaching Center ma swagat cha 🙏 Ke help chahiyo? Course fees, schedule, admission — jastai kura sodhnus!' },
];

var demoDefaultReply = 'Ramro prashna! Yesto details ko lagi hamlai direct message garnus — WhatsApp: <strong>9801234567</strong>. Chatbot Nepal le sabai kura handle garcha! 😊';

function demoSend(variant) {
  var inputEl = document.getElementById('demo-input-' + variant);
  var msgEl   = document.getElementById('demo-messages-' + variant);
  var text = (inputEl.value || '').trim();
  if (!text) return;

  var userBubble = document.createElement('div');
  userBubble.className = 'flex justify-end';
  userBubble.innerHTML = '<p class="bubble-user px-3 py-2 text-xs max-w-[90%]">' + escapeHtml(text) + '</p>';
  msgEl.appendChild(userBubble);
  inputEl.value = '';
  msgEl.scrollTop = msgEl.scrollHeight;

  var typing = document.createElement('div');
  typing.className = 'flex gap-2 items-end';
  typing.innerHTML = '<p class="bubble-bot px-3 py-2 text-xs text-[#888888]">...</p>';
  msgEl.appendChild(typing);
  msgEl.scrollTop = msgEl.scrollHeight;

  setTimeout(function() {
    typing.remove();
    var reply = demoDefaultReply;
    for (var i = 0; i < demoReplies.length; i++) {
      if (demoReplies[i].pattern.test(text)) { reply = demoReplies[i].reply; break; }
    }
    var botBubble = document.createElement('div');
    botBubble.className = 'flex gap-2 items-end';
    botBubble.innerHTML = '<p class="bubble-bot px-3 py-2 text-xs text-[#0d0d0d] max-w-[90%]">' + reply + '</p>';
    msgEl.appendChild(botBubble);
    msgEl.scrollTop = msgEl.scrollHeight;
  }, 800);
}

function escapeHtml(str) {
  return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

['demo-input-desktop','demo-input-mobile'].forEach(function(id) {
  var el = document.getElementById(id);
  if (el) {
    el.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') demoSend(id.includes('desktop') ? 'desktop' : 'mobile');
    });
  }
});

// ── Sticky mobile CTA bar ──
var stickyCta = document.getElementById('sticky-cta');
var heroSection = document.getElementById('about');
if (stickyCta && heroSection) {
  var heroObserver = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
      if (entry.isIntersecting) {
        stickyCta.classList.add('translate-y-full');
      } else {
        stickyCta.classList.remove('translate-y-full');
      }
    });
  }, { threshold: 0 });
  heroObserver.observe(heroSection);
}
</script>
</body>
</html>
