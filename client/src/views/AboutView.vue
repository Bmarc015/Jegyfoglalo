<template>
  <section class="about-page">
    <div class="about-hero">
      <p class="eyebrow mb-2">Jegyfoglalo Platform</p>
      <h1 class="hero-title mb-2">Foci meccsek, gyors jegyvasarlas, atlathato felulet</h1>
      <p class="hero-subtitle mb-0">
        Ez a projekt egy modern jegyfoglalo rendszer, ahol a felhasznalok meccseket bongeszhetnek, nap szerint
        valaszthatnak es egyszeruen indithatjak a vasarlast.
      </p>
    </div>

    <div class="about-grid">
      <article class="about-card">
        <h2 class="card-title">Mit tud a rendszer?</h2>
        <p class="card-text">Naptaros nezet, meccslista, jogosultsagkezelt admin feluletek, profil es jegykezeles.</p>
      </article>

      <article class="about-card">
        <h2 class="card-title">Kinek keszult?</h2>
        <p class="card-text">
          Iskolai vizsgaremek projektnek, ahol a cel egy valos, bovithetore tervezett webalkalmazas felepitese volt.
        </p>
      </article>

      <article class="about-card">
        <h2 class="card-title">Technikai alapok</h2>
        <p class="card-text">
          Frontend: Vue + Pinia + Bootstrap. Backend: Laravel API. Az adatok adatbazisbol jonnek, tokenes hitelesitessel.
        </p>
      </article>
    </div>

    <div class="meta-panel">
      <h2 class="meta-title mb-3">Kornyezeti informaciok</h2>
      <div class="meta-list">
        <div class="meta-row"><span>App neve</span><strong>{{ title || "Jegyfoglalo" }}</strong></div>
        <div class="meta-row"><span>Verzio</span><strong>{{ ver || "-" }}</strong></div>
        <div class="meta-row"><span>API URL</span><strong>{{ apiUrl || "-" }}</strong></div>
        <div class="meta-row"><span>Mode</span><strong>{{ mode }}</strong></div>
        <div class="meta-row"><span>DEV</span><strong>{{ dev }}</strong></div>
        <div class="meta-row"><span>PROD</span><strong>{{ prod }}</strong></div>
        <div class="meta-row"><span>SSR</span><strong>{{ ssr }}</strong></div>
      </div>
    </div>
  </section>
</template>


<script>

export default {
  data() {
    return {
      dev: import.meta.env.DEV,
      prod: import.meta.env.PROD,
      ssr: import.meta.env.SSR,
      mode: import.meta.env.MODE,
      title: import.meta.env.VITE_APP_TITLE,
      ver: import.meta.env.VITE_APP_VER,
      apiUrl: import.meta.env.VITE_API_URL
    }
  }
}
</script>

<style scoped>
.about-page {
  --about-bg-start: #f2f7ff;
  --about-bg-end: #e6f1ff;
  --about-ink: #122033;
  --about-muted: #3f5673;
  --about-accent: #0d6efd;
  --about-card: #ffffff;
  --about-border: #d8e3f3;
  --about-shadow: rgba(12, 39, 73, 0.12);
  font-family: "Poppins", "Segoe UI", "Trebuchet MS", sans-serif;
  min-height: calc(100vh - var(--app-menu-height, 92px));
  padding: 1.1rem;
  background: radial-gradient(circle at 8% 5%, #ffffff 0%, transparent 40%),
    linear-gradient(145deg, var(--about-bg-start), var(--about-bg-end));
  color: var(--about-ink);
}

.about-hero {
  border: 1px solid var(--about-border);
  border-radius: 16px;
  background: linear-gradient(120deg, #ffffff 0%, #f6fbff 100%);
  box-shadow: 0 14px 30px var(--about-shadow);
  padding: 1.2rem;
  margin-bottom: 1rem;
}

.eyebrow {
  display: inline-block;
  font-size: 0.78rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--about-accent);
}

.hero-title {
  font-size: clamp(1.35rem, 2.4vw, 2rem);
  font-weight: 800;
}

.hero-subtitle {
  color: var(--about-muted);
  max-width: 75ch;
}

.about-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 0.8rem;
  margin-bottom: 1rem;
}

.about-card {
  background: var(--about-card);
  border: 1px solid var(--about-border);
  border-radius: 14px;
  padding: 1rem;
  box-shadow: 0 8px 22px rgba(16, 48, 86, 0.08);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.about-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 14px 26px rgba(16, 48, 86, 0.14);
}

.card-title {
  font-size: 1.05rem;
  margin-bottom: 0.4rem;
}

.card-text {
  margin: 0;
  color: var(--about-muted);
}

.meta-panel {
  border: 1px solid var(--about-border);
  border-radius: 14px;
  background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
  box-shadow: 0 10px 24px rgba(11, 39, 79, 0.1);
  padding: 1rem;
}

.meta-title {
  font-size: 1.1rem;
}

.meta-list {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.45rem 1rem;
}

.meta-row {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  gap: 0.8rem;
  border-bottom: 1px dashed #d8e3f3;
  padding-bottom: 0.25rem;
}

.meta-row span {
  color: var(--about-muted);
}

@media (max-width: 992px) {
  .about-grid {
    grid-template-columns: 1fr;
  }

  .meta-list {
    grid-template-columns: 1fr;
  }
}
</style>
