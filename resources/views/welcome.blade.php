<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="ChatBot Nepal — AI chatbots for Nepali businesses. Answer customer questions 24/7 in Nepali, Hindi & English. Setup in 24 hours. Rs. 999/month.">
<title>ChatBot Nepal — AI Chatbot for Your Business Website</title>
<link rel="canonical" href="https://chatbotnepal.isoftroerp.com">
<meta property="og:title" content="ChatBot Nepal — AI Chatbot for Your Business Website">
<meta property="og:description" content="Answer customer queries 24/7 in Nepali, Hindi & English. No coding. Setup in 24 hours.">
<meta property="og:url" content="https://chatbotnepal.isoftroerp.com">
<meta property="og:type" content="website">

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
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0">

<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          'primary': '#00535b',
          'primary-c': '#006d77',
          'secondary': '#8e4e14',
          'surf-base': '#f8f9fa',
          'surf-low': '#f3f4f5',
          'surf-container': '#edeeef',
          'muted': '#3e494a',
          'outline-var': '#bec8ca',
        },
        fontFamily: {
          jakarta: ['"Plus Jakarta Sans"', 'sans-serif'],
          inter: ['Inter', 'sans-serif'],
        },
      }
    }
  }
</script>

<style>
  *, *::before, *::after { box-sizing: border-box; }
  html { scroll-behavior: smooth; }
  body { font-family: 'Inter', sans-serif; color: #191c1d; background: #f8f9fa; }
  h1, h2, h3, h4, h5 { font-family: 'Plus Jakarta Sans', sans-serif; }

  .material-symbols-outlined {
    font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    vertical-align: middle;
    display: inline-flex;
  }

  /* Shadows */
  .shadow-teal   { box-shadow: 0 20px 40px rgba(0,109,119,0.07); }
  .shadow-teal-sm{ box-shadow: 0 4px 16px rgba(0,109,119,0.08); }
  .shadow-teal-lg{ box-shadow: 0 32px 64px rgba(0,109,119,0.12); }

  /* Chat bubbles */
  .bubble-bot  { background:#e7e8e9; border-radius:1rem 1rem 1rem 0.2rem; }
  .bubble-user { background:#006d77; color:#fff; border-radius:1rem 1rem 0.2rem 1rem; }

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
  .ind-card { transition: box-shadow .2s ease, transform .2s ease; }
  .ind-card:hover { box-shadow:0 12px 32px rgba(0,109,119,0.10); transform:translateY(-3px); }
</style>
</head>
<body class="bg-surf-base">

<!-- ════════════════════════════════════
     NAV
════════════════════════════════════ -->
<nav class="fixed top-0 inset-x-0 z-50 bg-white/80 backdrop-blur-xl border-b border-outline-var/20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">

    <a href="/" class="font-jakarta font-extrabold text-xl text-primary tracking-tight">ChatBot Nepal</a>

    <div class="hidden md:flex items-center gap-8 text-sm font-medium text-muted">
      <a href="#about"   class="hover:text-primary transition-colors">About</a>
      <a href="#pricing" class="hover:text-primary transition-colors">Pricing</a>
      <a href="#faq"     class="hover:text-primary transition-colors">FAQ</a>
      <a href="#contact" class="hover:text-primary transition-colors">Contact</a>
    </div>

    <div class="hidden md:flex items-center gap-3">
      <a href="{{ route('login') }}"
         class="text-sm font-semibold text-primary hover:text-primary-c transition-colors px-4 py-2.5">
        Login
      </a>
      <a href="https://wa.me/9779811144402?text=I%20want%20a%20free%20demo%20for%20my%20business"
         target="_blank" rel="noopener"
         class="inline-flex items-center bg-primary-c hover:bg-primary text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
        Get Free Demo
      </a>
    </div>

    <button id="hamburger" class="md:hidden p-2 rounded-lg hover:bg-surf-low transition-colors" aria-label="Open menu">
      <span class="material-symbols-outlined text-primary">menu</span>
    </button>
  </div>
</nav>

<!-- Mobile drawer -->
<div id="drawer-overlay" class="fixed inset-0 z-40 bg-black/40 hidden"></div>
<div id="nav-drawer" class="fixed top-0 right-0 bottom-0 w-72 z-50 bg-white shadow-2xl flex flex-col">
  <div class="flex items-center justify-between px-5 py-4 border-b border-outline-var/20">
    <span class="font-jakarta font-bold text-primary">ChatBot Nepal</span>
    <button id="close-drawer" class="p-2 rounded-lg hover:bg-surf-low">
      <span class="material-symbols-outlined text-muted">close</span>
    </button>
  </div>
  <div class="flex flex-col gap-1 p-4 text-sm font-medium">
    <a href="#about"   class="drawer-link px-4 py-3 rounded-xl text-muted hover:bg-surf-low hover:text-primary transition-colors">About</a>
    <a href="#pricing" class="drawer-link px-4 py-3 rounded-xl text-muted hover:bg-surf-low hover:text-primary transition-colors">Pricing</a>
    <a href="#faq"     class="drawer-link px-4 py-3 rounded-xl text-muted hover:bg-surf-low hover:text-primary transition-colors">FAQ</a>
    <a href="#contact" class="drawer-link px-4 py-3 rounded-xl text-muted hover:bg-surf-low hover:text-primary transition-colors">Contact</a>
  </div>
  <div class="mt-auto p-5 flex flex-col gap-3">
    <a href="{{ route('login') }}"
       class="block text-center border-2 border-primary text-primary font-semibold py-3.5 rounded-xl hover:bg-primary hover:text-white transition-colors">
      Login
    </a>
    <a href="https://wa.me/9779811144402?text=I%20want%20a%20free%20demo%20for%20my%20business"
       target="_blank" rel="noopener"
       class="block text-center bg-primary-c text-white font-semibold py-3.5 rounded-xl">
      Get Free Demo
    </a>
  </div>
</div>

<!-- ════════════════════════════════════
     HERO
════════════════════════════════════ -->
<section class="bg-surf-low pt-28 pb-16 md:pt-36 md:pb-24" id="about">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-16">

      <!-- Left -->
      <div class="flex-1 lg:max-w-[58%] reveal">
        <span class="inline-flex items-center bg-primary-c text-white text-xs font-bold uppercase tracking-widest px-4 py-2 rounded-full mb-6">
          Now serving businesses across Nepal&nbsp;🇳🇵
        </span>

        <h1 class="font-jakarta font-extrabold text-4xl md:text-5xl text-primary leading-[1.1] mb-5">
          Your Customers Have Questions.<br>
          Your Website Should<br>
          Answer Them.
        </h1>

        <p class="text-lg text-muted leading-relaxed mb-8 max-w-lg">
          We build AI chatbots that handle customer queries, capture leads, and work 24/7 — in Nepali, Hindi &amp; English. No technical skills needed.
        </p>

        <div class="flex flex-col sm:flex-row gap-3 mb-5">
          <a href="https://wa.me/9779811144402?text=I%20want%20a%20free%20demo%20for%20my%20business"
             target="_blank" rel="noopener"
             class="inline-flex items-center justify-center gap-2 bg-primary-c hover:bg-primary text-white font-bold py-4 px-8 rounded-xl transition-colors text-base">
            Get Free Demo &rarr;
          </a>
          <a href="#pricing"
             class="inline-flex items-center justify-center border-2 border-primary text-primary font-semibold py-4 px-8 rounded-xl hover:bg-primary hover:text-white transition-colors text-base">
            View Pricing
          </a>
        </div>

        <p class="text-sm text-muted/70">Setup in 24 hours &nbsp;&middot;&nbsp; No coding required &nbsp;&middot;&nbsp; Cancel anytime</p>
      </div>

      <!-- Right — Chat mockup -->
      <div class="w-full max-w-sm mx-auto lg:mx-0 lg:flex-1 reveal" style="transition-delay:.15s">
        <div class="bg-white rounded-2xl overflow-hidden shadow-teal-lg lg:[transform:rotate(2deg)]">
          <!-- Header -->
          <div class="bg-primary-c px-4 py-3 flex items-center gap-3">
            <div class="w-9 h-9 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0">
              <span class="material-symbols-outlined text-white" style="font-size:18px">support_agent</span>
            </div>
            <div>
              <p class="text-white font-jakarta font-semibold text-sm">Everest Coaching Center</p>
              <div class="flex items-center gap-1.5 mt-0.5">
                <span class="w-1.5 h-1.5 bg-green-400 rounded-full"></span>
                <span class="text-white/70 text-xs">Online now</span>
              </div>
            </div>
          </div>

          <!-- Messages -->
          <div class="bg-white p-4 flex flex-col gap-3" style="min-height:280px">
            <div class="flex gap-2 items-end">
              <div class="w-6 h-6 bg-primary-c rounded-full flex-shrink-0 flex items-center justify-center">
                <span class="material-symbols-outlined text-white" style="font-size:13px">smart_toy</span>
              </div>
              <p class="bubble-bot px-3.5 py-2.5 text-sm text-[#191c1d] max-w-[80%]">
                Namaste! Everest Coaching Center ma swagat cha. Ke help chahiyo? 🙏
              </p>
            </div>

            <div class="flex justify-end">
              <p class="bubble-user px-3.5 py-2.5 text-sm max-w-[80%]">
                Morning batch ko fee kati ho?
              </p>
            </div>

            <div class="flex gap-2 items-end">
              <div class="w-6 h-6 bg-primary-c rounded-full flex-shrink-0 flex items-center justify-center">
                <span class="material-symbols-outlined text-white" style="font-size:13px">smart_toy</span>
              </div>
              <p class="bubble-bot px-3.5 py-2.5 text-sm text-[#191c1d] max-w-[80%]">
                Monthly fee <strong>Rs. 3,500</strong> ho. Admission Rs. 1,000 one-time. Ahile open cha! 🎉
              </p>
            </div>

            <div class="flex justify-end">
              <p class="bubble-user px-3.5 py-2.5 text-sm max-w-[80%]">IELTS course ni cha?</p>
            </div>

            <div class="flex gap-2 items-end">
              <div class="w-6 h-6 bg-primary-c rounded-full flex-shrink-0 flex items-center justify-center">
                <span class="material-symbols-outlined text-white" style="font-size:13px">smart_toy</span>
              </div>
              <p class="bubble-bot px-3.5 py-2.5 text-sm text-[#191c1d] max-w-[80%]">
                Cha! IELTS preparation course <strong>Rs. 8,000</strong>/month. Class Mon-Fri 7–9 AM. Free trial class available! 📚
              </p>
            </div>
          </div>

          <!-- Input -->
          <div class="bg-white border-t border-outline-var/30 px-4 py-3 flex gap-2">
            <input type="text" placeholder="Type your message..." disabled
              class="flex-1 bg-surf-low rounded-xl px-3 py-2 text-sm text-muted outline-none">
            <button class="bg-primary-c text-white p-2 rounded-xl flex-shrink-0">
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
<section class="bg-white py-16 md:py-24">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12 reveal">
      <h2 class="font-jakarta font-bold text-3xl md:text-4xl text-primary mb-3">You're Losing Customers Right Now</h2>
      <p class="text-muted text-lg">Here's what happens every day on your website</p>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
      <div class="bg-white rounded-3xl shadow-teal p-8 reveal">
        <div class="w-14 h-14 bg-surf-low rounded-2xl flex items-center justify-center mb-5">
          <span class="material-symbols-outlined text-primary-c" style="font-size:28px">schedule</span>
        </div>
        <h3 class="font-jakarta font-bold text-lg text-primary mb-2">Late-Night Queries</h3>
        <p class="text-muted text-sm leading-relaxed">Customers message at 10 PM. Nobody replies until morning. They go to your competitor.</p>
      </div>

      <div class="bg-white rounded-3xl shadow-teal p-8 reveal" style="transition-delay:.1s">
        <div class="w-14 h-14 bg-surf-low rounded-2xl flex items-center justify-center mb-5">
          <span class="material-symbols-outlined text-primary-c" style="font-size:28px">repeat</span>
        </div>
        <h3 class="font-jakarta font-bold text-lg text-primary mb-2">Repetitive Questions</h3>
        <p class="text-muted text-sm leading-relaxed">Your staff wastes hours answering the same 20 questions. "Kati parcha?" "Kata ho?" every single day.</p>
      </div>

      <div class="bg-white rounded-3xl shadow-teal p-8 reveal" style="transition-delay:.2s">
        <div class="w-14 h-14 bg-surf-low rounded-2xl flex items-center justify-center mb-5">
          <span class="material-symbols-outlined text-primary-c" style="font-size:28px">exit_to_app</span>
        </div>
        <h3 class="font-jakarta font-bold text-lg text-primary mb-2">Visitors Leaving</h3>
        <p class="text-muted text-sm leading-relaxed">Without instant answers, visitors browse and leave. You paid for that traffic — and lost them.</p>
      </div>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════
     SOLUTION
════════════════════════════════════ -->
<section class="bg-surf-low py-16 md:py-24">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-14 reveal">
      <h2 class="font-jakarta font-bold text-3xl md:text-4xl text-primary mb-3">Here's What We Do</h2>
    </div>

    <div class="grid md:grid-cols-3 gap-10 md:gap-16">
      <div class="reveal">
        <div class="font-jakarta font-extrabold text-[7rem] leading-none text-outline-var/50 select-none mb-[-2rem]">01</div>
        <div class="relative z-10">
          <h3 class="font-jakarta font-bold text-xl text-primary mb-3">Answers Customer Queries Instantly</h3>
          <p class="text-muted leading-relaxed">No more "We'll get back to you." Your chatbot knows your services, pricing, FAQs and answers in seconds.</p>
        </div>
      </div>

      <div class="reveal" style="transition-delay:.1s">
        <div class="font-jakarta font-extrabold text-[7rem] leading-none text-outline-var/50 select-none mb-[-2rem]">02</div>
        <div class="relative z-10">
          <h3 class="font-jakarta font-bold text-xl text-primary mb-3">Captures Leads While You Sleep</h3>
          <p class="text-muted leading-relaxed">Every midnight visitor becomes a potential customer. Chatbot collects their name, number, and what they need.</p>
        </div>
      </div>

      <div class="reveal" style="transition-delay:.2s">
        <div class="font-jakarta font-extrabold text-[7rem] leading-none text-outline-var/50 select-none mb-[-2rem]">03</div>
        <div class="relative z-10">
          <h3 class="font-jakarta font-bold text-xl text-primary mb-3">Speaks Your Customer's Language</h3>
          <p class="text-muted leading-relaxed">Auto-detects Nepali, Hindi, or English. Customers type in their language, chatbot replies in the same.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════
     HOW IT WORKS
════════════════════════════════════ -->
<section class="bg-white py-16 md:py-24">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-14 reveal">
      <h2 class="font-jakarta font-bold text-3xl md:text-4xl text-primary mb-3">Live in 24 Hours</h2>
      <p class="text-muted text-lg">We don't just give you a tool, we build the system for your business.</p>
    </div>

    <!-- Desktop timeline -->
    <div class="hidden md:grid grid-cols-4 gap-8 relative">
      <div class="absolute top-10 left-[12.5%] right-[12.5%] border-t-2 border-dashed border-outline-var z-0"></div>

      <div class="flex flex-col items-center text-center relative z-10 reveal">
        <div class="w-20 h-20 bg-surf-low rounded-full flex items-center justify-center mb-5 shadow-teal-sm">
          <span class="material-symbols-outlined text-primary-c" style="font-size:30px">mic</span>
        </div>
        <h3 class="font-jakarta font-bold text-sm text-primary mb-2">We Interview You</h3>
        <p class="text-muted text-sm">30 min call — we learn your business inside out</p>
      </div>

      <div class="flex flex-col items-center text-center relative z-10 reveal" style="transition-delay:.1s">
        <div class="w-20 h-20 bg-primary-c rounded-full flex items-center justify-center mb-5 shadow-teal">
          <span class="material-symbols-outlined text-white" style="font-size:30px">psychology</span>
        </div>
        <h3 class="font-jakarta font-bold text-sm text-primary mb-2">Knowledge Build</h3>
        <p class="text-muted text-sm">We build an AI knowledge base with your exact info</p>
      </div>

      <div class="flex flex-col items-center text-center relative z-10 reveal" style="transition-delay:.2s">
        <div class="w-20 h-20 bg-surf-low rounded-full flex items-center justify-center mb-5 shadow-teal-sm">
          <span class="material-symbols-outlined text-primary-c" style="font-size:30px">code</span>
        </div>
        <h3 class="font-jakarta font-bold text-sm text-primary mb-2">One Line of Code</h3>
        <p class="text-muted text-sm">Paste one line on your website. That's it.</p>
      </div>

      <div class="flex flex-col items-center text-center relative z-10 reveal" style="transition-delay:.3s">
        <div class="w-20 h-20 bg-surf-low rounded-full flex items-center justify-center mb-5 shadow-teal-sm">
          <span class="material-symbols-outlined text-primary-c" style="font-size:30px">support_agent</span>
        </div>
        <h3 class="font-jakarta font-bold text-sm text-primary mb-2">24/7 Answers</h3>
        <p class="text-muted text-sm">Customers get instant answers, you get more sales</p>
      </div>
    </div>

    <!-- Mobile timeline -->
    <div class="md:hidden flex flex-col">
      <div class="flex gap-5">
        <div class="flex flex-col items-center">
          <div class="w-14 h-14 bg-surf-low rounded-full flex items-center justify-center flex-shrink-0">
            <span class="material-symbols-outlined text-primary-c" style="font-size:22px">mic</span>
          </div>
          <div class="w-0.5 flex-1 bg-outline-var/40 my-2" style="border-left:2px dashed #bec8ca; width:0;"></div>
        </div>
        <div class="pt-2 pb-8">
          <h3 class="font-jakarta font-bold text-primary mb-1">We Interview You</h3>
          <p class="text-muted text-sm">30 min call — we learn your business</p>
        </div>
      </div>

      <div class="flex gap-5">
        <div class="flex flex-col items-center">
          <div class="w-14 h-14 bg-primary-c rounded-full flex items-center justify-center flex-shrink-0 shadow-teal-sm">
            <span class="material-symbols-outlined text-white" style="font-size:22px">psychology</span>
          </div>
          <div class="w-0.5 flex-1 my-2" style="border-left:2px dashed #bec8ca; width:0;"></div>
        </div>
        <div class="pt-2 pb-8">
          <h3 class="font-jakarta font-bold text-primary mb-1">Knowledge Build</h3>
          <p class="text-muted text-sm">We build AI knowledge base with your exact info</p>
        </div>
      </div>

      <div class="flex gap-5">
        <div class="flex flex-col items-center">
          <div class="w-14 h-14 bg-surf-low rounded-full flex items-center justify-center flex-shrink-0">
            <span class="material-symbols-outlined text-primary-c" style="font-size:22px">code</span>
          </div>
          <div class="w-0.5 flex-1 my-2" style="border-left:2px dashed #bec8ca; width:0;"></div>
        </div>
        <div class="pt-2 pb-8">
          <h3 class="font-jakarta font-bold text-primary mb-1">One Line of Code</h3>
          <p class="text-muted text-sm">Paste one line on your website. Done.</p>
        </div>
      </div>

      <div class="flex gap-5">
        <div class="flex flex-col items-center">
          <div class="w-14 h-14 bg-surf-low rounded-full flex items-center justify-center flex-shrink-0">
            <span class="material-symbols-outlined text-primary-c" style="font-size:22px">support_agent</span>
          </div>
        </div>
        <div class="pt-2">
          <h3 class="font-jakarta font-bold text-primary mb-1">24/7 Answers</h3>
          <p class="text-muted text-sm">Customers get instant answers, you get more sales</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════
     LIVE DEMO
════════════════════════════════════ -->
<section class="bg-surf-low py-16 md:py-24">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12 reveal">
      <h2 class="font-jakarta font-bold text-3xl md:text-4xl text-primary mb-3">See It in Action</h2>
      <p class="text-muted text-lg">Real conversation from a local coaching institute</p>
    </div>

    <!-- Browser mockup -->
    <div class="bg-white rounded-3xl shadow-teal overflow-hidden reveal">
      <!-- Chrome bar -->
      <div class="bg-surf-container px-4 py-3 flex items-center gap-3 border-b border-outline-var/20">
        <div class="flex gap-1.5">
          <span class="w-3 h-3 rounded-full bg-red-400 inline-block"></span>
          <span class="w-3 h-3 rounded-full bg-yellow-400 inline-block"></span>
          <span class="w-3 h-3 rounded-full bg-green-400 inline-block"></span>
        </div>
        <div class="flex-1 bg-white rounded-lg px-3 py-1.5 text-xs text-muted font-mono border border-outline-var/30">
          everestcoaching.com.np
        </div>
      </div>

      <!-- Page body -->
      <div class="relative bg-surf-low overflow-hidden" style="min-height:440px">
        <!-- Blurred fake page -->
        <div class="p-8 opacity-20 select-none pointer-events-none">
          <div class="h-5 bg-primary rounded w-48 mb-4"></div>
          <div class="h-3 bg-muted rounded w-72 mb-2"></div>
          <div class="h-3 bg-muted rounded w-64 mb-2"></div>
          <div class="h-3 bg-muted rounded w-56 mb-8"></div>
          <div class="flex gap-3">
            <div class="h-8 w-28 bg-primary rounded-xl"></div>
            <div class="h-8 w-24 bg-muted/40 rounded-xl"></div>
          </div>
        </div>

        <!-- Floating chat widget bottom-right -->
        <div class="absolute bottom-4 right-4 w-72 bg-white rounded-2xl overflow-hidden" style="box-shadow:0 8px 32px rgba(0,109,119,0.16)">
          <div class="bg-primary-c px-3 py-2.5 flex items-center gap-2">
            <div class="w-7 h-7 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0">
              <span class="material-symbols-outlined text-white" style="font-size:14px">smart_toy</span>
            </div>
            <div>
              <p class="text-white font-jakarta font-semibold text-xs">Everest Coaching</p>
              <div class="flex items-center gap-1">
                <span class="w-1.5 h-1.5 bg-green-400 rounded-full inline-block"></span>
                <span class="text-white/70 text-[10px]">Online</span>
              </div>
            </div>
          </div>

          <div class="bg-white p-3 flex flex-col gap-2">
            <p class="bubble-bot px-3 py-2 text-xs text-[#191c1d] max-w-[90%]">Namaste! Hamro coaching center bare kei sodhnu cha?</p>
            <div class="flex justify-end">
              <p class="bubble-user px-3 py-2 text-xs max-w-[90%]">Timro coaching ma class kati bajey suru huncha?</p>
            </div>
            <p class="bubble-bot px-3 py-2 text-xs text-[#191c1d] max-w-[90%]">Morning batch <strong>6:30 AM</strong>, evening batch <strong>4:00 PM</strong>. Kun batch ma interest cha?</p>
            <div class="flex justify-end">
              <p class="bubble-user px-3 py-2 text-xs max-w-[90%]">Morning batch ko fee kati ho?</p>
            </div>
            <p class="bubble-bot px-3 py-2 text-xs text-[#191c1d] max-w-[90%]">Monthly fee <strong>Rs. 3,500</strong>. Admission Rs. 1,000. Ahile open cha, join garnu huncha?</p>
            <div class="flex justify-end">
              <p class="bubble-user px-3 py-2 text-xs max-w-[90%]">IELTS course ko bare ma bhannu na</p>
            </div>
            <p class="bubble-bot px-3 py-2 text-xs text-[#191c1d] max-w-[90%]">IELTS Preparation <strong>Rs. 8,000/month</strong>. Mon-Fri 7–9 AM. Free trial class available. Book garnu huncha?</p>
          </div>

          <div class="bg-white border-t border-outline-var/20 px-3 py-2 flex gap-2">
            <input type="text" placeholder="Type your message..." disabled class="flex-1 bg-surf-low rounded-lg px-2.5 py-1.5 text-xs text-muted outline-none">
            <button class="bg-primary-c text-white p-1.5 rounded-lg">
              <span class="material-symbols-outlined" style="font-size:14px">send</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="text-center mt-10 reveal">
      <p class="text-muted mb-6">This is a real AI chatbot we built. Want one for your business?</p>
      <a href="https://wa.me/9779811144402?text=I%20want%20a%20free%20demo%20for%20my%20business"
         target="_blank" rel="noopener"
         class="inline-flex items-center gap-2 bg-primary-c hover:bg-primary text-white font-bold py-4 px-8 rounded-xl transition-colors">
        Get Your Free Demo
      </a>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════
     INDUSTRIES
════════════════════════════════════ -->
<section class="bg-white py-16 md:py-24">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12 reveal">
      <h2 class="font-jakarta font-bold text-3xl md:text-4xl text-primary mb-3">Built for Businesses Like Yours</h2>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 md:gap-6">
      <div class="ind-card bg-white border border-outline-var/30 rounded-2xl p-6 text-center reveal">
        <div class="w-12 h-12 bg-surf-low rounded-xl flex items-center justify-center mx-auto mb-4">
          <span class="material-symbols-outlined text-primary-c">school</span>
        </div>
        <h3 class="font-jakarta font-semibold text-primary text-sm mb-1">Coaching Centers</h3>
        <p class="text-muted text-xs leading-relaxed">Course fees, schedules, admission queries</p>
      </div>

      <div class="ind-card bg-white border border-outline-var/30 rounded-2xl p-6 text-center reveal" style="transition-delay:.05s">
        <div class="w-12 h-12 bg-surf-low rounded-xl flex items-center justify-center mx-auto mb-4">
          <span class="material-symbols-outlined text-primary-c">hotel</span>
        </div>
        <h3 class="font-jakarta font-semibold text-primary text-sm mb-1">Hotels &amp; Restaurants</h3>
        <p class="text-muted text-xs leading-relaxed">Booking, menu, room availability</p>
      </div>

      <div class="ind-card bg-white border border-outline-var/30 rounded-2xl p-6 text-center reveal" style="transition-delay:.1s">
        <div class="w-12 h-12 bg-surf-low rounded-xl flex items-center justify-center mx-auto mb-4">
          <span class="material-symbols-outlined text-primary-c">local_hospital</span>
        </div>
        <h3 class="font-jakarta font-semibold text-primary text-sm mb-1">Clinics</h3>
        <p class="text-muted text-xs leading-relaxed">Appointments, doctor schedules, services</p>
      </div>

      <div class="ind-card bg-white border border-outline-var/30 rounded-2xl p-6 text-center reveal" style="transition-delay:.15s">
        <div class="w-12 h-12 bg-surf-low rounded-xl flex items-center justify-center mx-auto mb-4">
          <span class="material-symbols-outlined text-primary-c">storefront</span>
        </div>
        <h3 class="font-jakarta font-semibold text-primary text-sm mb-1">Retail</h3>
        <p class="text-muted text-xs leading-relaxed">Product info, pricing, store hours</p>
      </div>

      <div class="ind-card bg-white border border-outline-var/30 rounded-2xl p-6 text-center reveal" style="transition-delay:.2s">
        <div class="w-12 h-12 bg-surf-low rounded-xl flex items-center justify-center mx-auto mb-4">
          <span class="material-symbols-outlined text-primary-c">campaign</span>
        </div>
        <h3 class="font-jakarta font-semibold text-primary text-sm mb-1">Marketing Agencies</h3>
        <p class="text-muted text-xs leading-relaxed">Service details, portfolio, booking</p>
      </div>

      <div class="ind-card bg-white border border-outline-var/30 rounded-2xl p-6 text-center reveal" style="transition-delay:.25s">
        <div class="w-12 h-12 bg-surf-low rounded-xl flex items-center justify-center mx-auto mb-4">
          <span class="material-symbols-outlined text-primary-c">real_estate_agent</span>
        </div>
        <h3 class="font-jakarta font-semibold text-primary text-sm mb-1">Real Estate</h3>
        <p class="text-muted text-xs leading-relaxed">Property details, pricing, visits</p>
      </div>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════
     PRICING
════════════════════════════════════ -->
<section class="py-16 md:py-24" id="pricing" style="background:linear-gradient(150deg,#f3f4f5 0%,#e4eeef 100%)">
  <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-14 reveal">
      <h2 class="font-jakarta font-bold text-3xl md:text-4xl text-primary mb-3">Simple, Honest Pricing</h2>
      <p class="text-muted">One plan. Everything included. No surprises.</p>
    </div>

    <div class="relative reveal">
      <div class="absolute -top-4 inset-x-0 flex justify-center z-10">
        <span class="bg-secondary text-white text-xs font-extrabold uppercase tracking-widest px-5 py-2 rounded-full">
          Early Adopter Price
        </span>
      </div>

      <div class="bg-white rounded-3xl shadow-teal-lg p-8 md:p-10 pt-12">
        <div class="text-center mb-6">
          <h3 class="font-jakarta font-bold text-2xl text-primary mb-1">Starter Plan</h3>
          <p class="text-muted text-sm">Everything you need. No hidden fees.</p>
        </div>

        <div class="text-center mb-2">
          <div class="inline-flex items-end gap-1">
            <span class="font-jakarta font-extrabold text-6xl text-primary leading-none">Rs. 999</span>
            <span class="text-muted text-sm mb-2">/month</span>
          </div>
        </div>
        <p class="text-center text-muted text-sm mb-8">+ Rs. 3,000 one-time setup &nbsp;·&nbsp; live in 24 hrs</p>

        <ul class="flex flex-col gap-3.5 mb-8">
          <li class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary-c flex-shrink-0" style="font-size:20px">check_circle</span>
            <span class="text-muted text-sm">Custom AI Training on your data</span>
          </li>
          <li class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary-c flex-shrink-0" style="font-size:20px">check_circle</span>
            <span class="text-muted text-sm">Nepali &amp; English Support</span>
          </li>
          <li class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary-c flex-shrink-0" style="font-size:20px">check_circle</span>
            <span class="text-muted text-sm">Lead Capture Dashboard</span>
          </li>
          <li class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary-c flex-shrink-0" style="font-size:20px">check_circle</span>
            <span class="text-muted text-sm">Weekly Performance Audits</span>
          </li>
          <li class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary-c flex-shrink-0" style="font-size:20px">check_circle</span>
            <span class="text-muted text-sm">Monthly Knowledge Base Updates</span>
          </li>
        </ul>

        <a href="https://wa.me/9779811144402?text=I%20want%20a%20free%20demo%20for%20my%20business"
           target="_blank" rel="noopener"
           class="block text-center w-full bg-secondary hover:opacity-90 text-white font-bold py-4 rounded-xl transition-opacity text-base">
          Claim This Offer Now
        </a>
      </div>
    </div>

    <p class="text-center text-sm text-muted mt-5">
      Limited to first 10 clients &nbsp;&middot;&nbsp; No contracts &nbsp;&middot;&nbsp; Cancel anytime
    </p>
  </div>
</section>

<!-- ════════════════════════════════════
     SOCIAL PROOF (Honest)
════════════════════════════════════ -->
<section class="bg-surf-low py-16 md:py-20">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
    <div class="reveal mb-10">
      <h2 class="font-jakarta font-bold text-3xl text-primary mb-4">Be One of Our First 10 Clients</h2>
      <p class="text-muted text-lg leading-relaxed max-w-2xl mx-auto">
        We are currently in beta and looking for 10 visionary Nepali businesses to be our early partners. You'll get VIP support, the lowest rate locked in permanently, and a chatbot built with extra care.
      </p>
    </div>

    <div class="grid md:grid-cols-3 gap-5">
      <div class="bg-white rounded-2xl p-6 flex flex-col items-center gap-3 shadow-teal-sm reveal">
        <div class="w-12 h-12 bg-surf-low rounded-xl flex items-center justify-center">
          <span class="material-symbols-outlined text-primary-c">priority_high</span>
        </div>
        <h3 class="font-jakarta font-semibold text-primary text-sm">Priority 24/7 Support</h3>
        <p class="text-muted text-xs">Direct access to our team, always</p>
      </div>

      <div class="bg-white rounded-2xl p-6 flex flex-col items-center gap-3 shadow-teal-sm reveal" style="transition-delay:.1s">
        <div class="w-12 h-12 bg-surf-low rounded-xl flex items-center justify-center">
          <span class="material-symbols-outlined text-primary-c">monitoring</span>
        </div>
        <h3 class="font-jakarta font-semibold text-primary text-sm">Weekly Performance Audits</h3>
        <p class="text-muted text-xs">We review your chatbot every week</p>
      </div>

      <div class="bg-white rounded-2xl p-6 flex flex-col items-center gap-3 shadow-teal-sm reveal" style="transition-delay:.2s">
        <div class="w-12 h-12 bg-surf-low rounded-xl flex items-center justify-center">
          <span class="material-symbols-outlined text-primary-c">location_on</span>
        </div>
        <h3 class="font-jakarta font-semibold text-primary text-sm">Nepal-Based Team</h3>
        <p class="text-muted text-xs">We speak your language, literally</p>
      </div>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════
     FAQ
════════════════════════════════════ -->
<section class="bg-white py-16 md:py-24" id="faq">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12 reveal">
      <h2 class="font-jakarta font-bold text-3xl md:text-4xl text-primary mb-3">Common Questions</h2>
    </div>

    <div class="flex flex-col gap-2">
      <div class="faq-item bg-surf-low rounded-2xl overflow-hidden reveal">
        <button class="faq-btn w-full flex items-center justify-between px-5 py-4 text-left gap-4">
          <span class="font-jakarta font-semibold text-primary text-sm md:text-base">How long does setup take?</span>
          <span class="material-symbols-outlined faq-icon text-primary-c flex-shrink-0">add</span>
        </button>
        <div class="faq-answer">
          <p class="px-5 pb-5 text-muted text-sm leading-relaxed">24 hours from our interview call. We collect your info, build the knowledge base, and deploy. You paste one line of code.</p>
        </div>
      </div>

      <div class="faq-item bg-surf-low rounded-2xl overflow-hidden reveal">
        <button class="faq-btn w-full flex items-center justify-between px-5 py-4 text-left gap-4">
          <span class="font-jakarta font-semibold text-primary text-sm md:text-base">Does it understand Nepali in Roman letters?</span>
          <span class="material-symbols-outlined faq-icon text-primary-c flex-shrink-0">add</span>
        </button>
        <div class="faq-answer">
          <p class="px-5 pb-5 text-muted text-sm leading-relaxed">Yes. Customers can type "fee kati ho?" in romanized Nepali. The chatbot understands and replies perfectly.</p>
        </div>
      </div>

      <div class="faq-item bg-surf-low rounded-2xl overflow-hidden reveal">
        <button class="faq-btn w-full flex items-center justify-between px-5 py-4 text-left gap-4">
          <span class="font-jakarta font-semibold text-primary text-sm md:text-base">Can I track leads and queries?</span>
          <span class="material-symbols-outlined faq-icon text-primary-c flex-shrink-0">add</span>
        </button>
        <div class="faq-answer">
          <p class="px-5 pb-5 text-muted text-sm leading-relaxed">Yes. You get a dashboard showing every visitor query, every lead captured, and every contact detail collected.</p>
        </div>
      </div>

      <div class="faq-item bg-surf-low rounded-2xl overflow-hidden reveal">
        <button class="faq-btn w-full flex items-center justify-between px-5 py-4 text-left gap-4">
          <span class="font-jakarta font-semibold text-primary text-sm md:text-base">Is there a contract?</span>
          <span class="material-symbols-outlined faq-icon text-primary-c flex-shrink-0">add</span>
        </button>
        <div class="faq-answer">
          <p class="px-5 pb-5 text-muted text-sm leading-relaxed">No. Month-to-month. Cancel anytime. No lock-in.</p>
        </div>
      </div>

      <div class="faq-item bg-surf-low rounded-2xl overflow-hidden reveal">
        <button class="faq-btn w-full flex items-center justify-between px-5 py-4 text-left gap-4">
          <span class="font-jakarta font-semibold text-primary text-sm md:text-base">What if my pricing or services change?</span>
          <span class="material-symbols-outlined faq-icon text-primary-c flex-shrink-0">add</span>
        </button>
        <div class="faq-answer">
          <p class="px-5 pb-5 text-muted text-sm leading-relaxed">Message us. We update your chatbot's knowledge base same day. That's exactly why our monthly fee exists — we maintain everything.</p>
        </div>
      </div>

      <div class="faq-item bg-surf-low rounded-2xl overflow-hidden reveal">
        <button class="faq-btn w-full flex items-center justify-between px-5 py-4 text-left gap-4">
          <span class="font-jakarta font-semibold text-primary text-sm md:text-base">Will it slow my website?</span>
          <span class="material-symbols-outlined faq-icon text-primary-c flex-shrink-0">add</span>
        </button>
        <div class="faq-answer">
          <p class="px-5 pb-5 text-muted text-sm leading-relaxed">No. Single lightweight script, loads asynchronously. Zero impact on your site speed.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════
     FINAL CTA
════════════════════════════════════ -->
<section class="bg-primary py-16 md:py-24" id="contact">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center reveal">
    <h2 class="font-jakarta font-extrabold text-3xl md:text-4xl text-white mb-4">
      Ready to Stop Losing Customers?
    </h2>
    <p class="text-white/80 text-lg mb-10 max-w-xl mx-auto">
      Join the 10 exclusive beta partners today and bring your website to life.
    </p>

    <div class="flex flex-col sm:flex-row gap-4 justify-center">
      <a href="https://wa.me/9779811144402?text=I%20want%20to%20talk%20to%20an%20agent"
         target="_blank" rel="noopener"
         class="inline-flex items-center justify-center gap-2 text-white font-bold py-4 px-8 rounded-xl transition-opacity hover:opacity-90"
         style="background:#25D366">
        <span class="material-symbols-outlined" style="font-size:20px">chat</span>
        Talk to Our Agent Now
      </a>
      <a href="mailto:info@isoftro.com"
         class="inline-flex items-center justify-center gap-2 border-2 border-white text-white font-bold py-4 px-8 rounded-xl hover:bg-white hover:text-primary transition-colors">
        <span class="material-symbols-outlined" style="font-size:20px">mail</span>
        Send Email
      </a>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════
     FOOTER
════════════════════════════════════ -->
<footer class="bg-surf-container py-10">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col md:flex-row items-center justify-between gap-5">
      <span class="font-jakarta font-extrabold text-xl text-primary">ChatBot Nepal</span>
      <div class="flex flex-wrap items-center justify-center gap-6 text-sm text-muted">
        <a href="{{ route('privacy-policy') }}" class="hover:text-primary transition-colors">Privacy Policy</a>
        <a href="{{ route('terms') }}" class="hover:text-primary transition-colors">Terms of Service</a>
        <a href="mailto:info@isoftro.com" class="hover:text-primary transition-colors">Contact Support</a>
      </div>
    </div>
    <div class="border-t border-outline-var/40 mt-6 pt-6 text-center">
      <p class="text-muted text-sm">&copy; 2026 ChatBot Nepal. Kathmandu, Nepal 🇳🇵</p>
      <p class="text-muted text-xs mt-2">
        Built by <a href="https://isoftroerp.com/" target="_blank" rel="noopener" class="text-primary font-semibold hover:underline">ISoftroERP</a>
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
<script src="https://chatbotnepal.isoftroerp.com/widget.js" data-site-id="chatbotnepal-buwgr2" defer></script>
</body>
</html>
