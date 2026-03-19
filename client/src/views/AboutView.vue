<template>
  <section class="about-page">
    <div class="about-hero">
      <p class="eyebrow mb-2">Santiago Bernabeu</p>
      <h1 class="hero-title mb-2">The heartbeat of Madrid football</h1>
      <p class="hero-subtitle mb-0">
        From historic nights to modern matchdays, the Santiago Bernabeu is more than a stadium. It is a living landmark
        where tradition, innovation, and passion meet.
      </p>
    </div>

    <div class="about-hero-image">
      <img src="/about-santiago.jpg" alt="Santiago Bernabeu stadium" />
    </div>

    <div class="about-split">
      <div class="about-story">
        <h2 class="section-title">A Legacy Reimagined</h2>
        <p class="section-text">
          First opened in 1947, the Bernabeu has hosted generations of unforgettable moments. Today, the renewed venue
          blends classic identity with a cutting-edge experience designed for every fan.
        </p>
        <div class="story-points">
          <div class="story-item">
            <h3 class="story-title">Iconic atmosphere</h3>
            <p class="story-text">A roaring crowd, a global stage, and a skyline view of Madrid.</p>
          </div>
          <div class="story-item">
            <h3 class="story-title">Modern comfort</h3>
            <p class="story-text">Upgraded hospitality, better sightlines, and a seamless visitor journey.</p>
          </div>
        </div>
      </div>

      <div class="about-highlight">
        <div class="highlight-card">
          <p class="highlight-label">Stadium Highlights</p>
          <ul class="highlight-list">
            <li>Legendary matchday atmosphere</li>
            <li>Immersive museum and tour route</li>
            <li>Expanded seating and premium zones</li>
            <li>New roof and multimedia experience</li>
          </ul>
        </div>
        <div class="highlight-card soft">
          <p class="highlight-label">Where to Find It</p>
          <p class="highlight-text">
            Located on Paseo de la Castellana, the Bernabeu sits at the heart of Madrid with fast metro access and a
            vibrant matchday neighborhood.
          </p>
        </div>
      </div>
    </div>

    <div class="about-values">
      <div class="values-head">
        <h2 class="section-title">Inside the Bernabeu</h2>
        <p class="section-text">A glimpse of the arena that shapes Real Madrid history.</p>
      </div>
      <div class="values-grid">
        <article class="value-card">
          <h3 class="value-title">Matchday energy</h3>
          <p class="value-text">
            Chants, banners, and a crowd that turns every game into a spectacle.
          </p>
        </article>
        <article class="value-card">
          <h3 class="value-title">Tour experience</h3>
          <p class="value-text">
            Walk the same halls as legends and see the trophies up close.
          </p>
        </article>
        <article class="value-card">
          <h3 class="value-title">City landmark</h3>
          <p class="value-text">
            A modern icon on the Castellana, surrounded by restaurants, museums, and lively streets.
          </p>
        </article>
      </div>
    </div>
  </section>
</template>


<script>

export default {
  data() {
    return {}
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
  padding: 0.9rem;
  background: radial-gradient(circle at 8% 5%, #ffffff 0%, transparent 40%),
    linear-gradient(145deg, var(--about-bg-start), var(--about-bg-end));
  color: var(--about-ink);
}

.about-hero {
  border: 1px solid var(--about-border);
  border-radius: 16px;
  background: linear-gradient(120deg, #ffffff 0%, #f6fbff 100%);
  box-shadow: 0 14px 30px var(--about-shadow);
  padding: 1rem;
  margin-bottom: 0.75rem;
}

.about-hero-image {
  border-radius: 18px;
  overflow: hidden;
  border: 1px solid var(--about-border);
  box-shadow: 0 12px 26px rgba(12, 39, 73, 0.14);
  margin-bottom: 0.75rem;
}

.about-hero-image img {
  width: 100%;
  height: min(46vh, 360px);
  object-fit: cover;
  object-position: center 70%;
  display: block;
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

.about-split {
  display: grid;
  grid-template-columns: minmax(0, 1.3fr) minmax(0, 1fr);
  gap: 0.75rem;
  margin-bottom: 0.75rem;
}

.about-story,
.about-highlight,
.about-values {
  border: 1px solid var(--about-border);
  border-radius: 16px;
  background: var(--about-card);
  box-shadow: 0 10px 24px rgba(11, 39, 79, 0.1);
  padding: 0.9rem;
}

.section-title {
  font-size: 1.1rem;
  margin-bottom: 0.35rem;
}

.section-text {
  margin: 0 0 0.55rem;
  color: var(--about-muted);
}

.story-points {
  display: grid;
  gap: 0.55rem;
}

.story-item {
  padding: 0.7rem;
  border-radius: 12px;
  background: linear-gradient(120deg, #f7fbff 0%, #ffffff 100%);
  border: 1px solid #e2ecfb;
}

.story-title {
  font-size: 0.98rem;
  margin-bottom: 0.3rem;
}

.story-text {
  margin: 0;
  color: var(--about-muted);
}

.about-highlight {
  display: grid;
  gap: 0.6rem;
  background: linear-gradient(180deg, #ffffff 0%, #f6faff 100%);
}

.highlight-card {
  border-radius: 12px;
  padding: 0.75rem;
  border: 1px solid #e1ebfb;
  background: #ffffff;
  box-shadow: 0 8px 18px rgba(12, 39, 73, 0.08);
}

.highlight-card.soft {
  background: #f5f9ff;
}

.highlight-label {
  margin: 0 0 0.45rem;
  font-weight: 700;
  color: var(--about-accent);
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-size: 0.75rem;
}

.highlight-list {
  margin: 0;
  padding-left: 1.2rem;
  color: var(--about-muted);
}

.highlight-text {
  margin: 0;
  color: var(--about-muted);
}

.about-values {
  margin-bottom: 0.3rem;
  background: linear-gradient(160deg, #ffffff 0%, #f7fbff 100%);
}

.values-head {
  margin-bottom: 0.4rem;
}

.values-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 0.6rem;
}

.value-card {
  background: #ffffff;
  border: 1px solid #e0e9f8;
  border-radius: 12px;
  padding: 0.75rem;
  box-shadow: 0 8px 18px rgba(12, 39, 73, 0.08);
}

.value-title {
  font-size: 1rem;
  margin-bottom: 0.35rem;
}

.value-text {
  margin: 0;
  color: var(--about-muted);
}

@media (max-width: 992px) {
  .about-split {
    grid-template-columns: 1fr;
  }

  .values-grid {
    grid-template-columns: 1fr;
  }
}
</style>
