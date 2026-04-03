<template>
  <div class="home-page">
    <section class="hero">
      <div class="hero-left">
        <p class="badge">Gudra notikumu plānošana</p>
        <h1>Plāno savu laiku ar modernu un pārskatāmu kalendāru.</h1>
        <p class="subtitle">
          Organizē notikumus, pārvaldi atgādinājumus un glabā visu vienuviet.
        </p>

        <div class="hero-buttons">
          <router-link to="/register" class="primary-btn">Sākt</router-link>
          <router-link to="/login" class="secondary-btn">Pieslēgties</router-link>
        </div>
      </div>

      <div class="hero-right">
        <router-link to="/dashboard" class="calendar-link">
          <div class="mockup-card">
            <div class="mockup-top">
              <span>January 2026</span>
              <button type="button">+ Add</button>
            </div>

            <div class="calendar-grid">
              <div v-for="day in 35" :key="day" class="cell">
                <div v-if="[4, 10, 18, 26].includes(day)" class="event-tag">
                  Event
                </div>
              </div>
            </div>
          </div>
        </router-link>
      </div>
    </section>

    <section id="features" class="features">
      <div class="section-title">
        <h2>Galvenās funkcijas</h2>
        <p>Viss nepieciešamais ērtai plānošanai:</p>
      </div>

      <div class="feature-grid">
        <div class="feature-card">
          <h3>Notikumu pārvaldība</h3>
          <p>Izveido, rediģē un dzēs notikumus</p>
        </div>

        <div class="feature-card">
          <h3>Lietotāju konti</h3>
          <p>Pieteikšanās un reģistrācijas lapas gatavas backend integrācijai.</p>
        </div>

        <div class="feature-card">
          <h3>Meklēšana un filtri</h3>
          <p>Ātri atrodi notikumus un ērtāk organizē savu laiku.</p>
        </div>
      </div>
    </section>

    <section id="about" class="about">
      <div class="about-box">
        <div>
          <h2>Izveidots produktivitātei</h2>
          <p>
            Šis plānotājs palīdz lietotājiem pārvaldīt ikdienas uzdevumus,
            atgādinājumus un gaidāmos notikumus vienā vietā.
          </p>
        </div>
      </div>
    </section>

    <footer class="footer">
      <div class="footer-content">
        <div>
          <h3>Catlendar</h3>
          <p>Notikumu plānotājs, lai organizētu savu laiku efektīvi.</p>
        </div>

        <div class="footer-links">
          <a href="#features">Funkcijas</a>
          <a href="#about">Par projektu</a>
          <router-link to="/login">Pieslēgties</router-link>
          <router-link to="/register">Reģistrēties</router-link>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
.home-page {
  background: #f8f7fc;
  min-height: 100vh;
  color: #1f2937;
}

.hero {
  max-width: 1200px;
  margin: 0 auto;
  padding: 70px 24px;
  display: grid;
  grid-template-columns: 1.1fr 1fr;
  gap: 40px;
  align-items: center;
}

.badge {
  display: inline-block;
  background: #ede9fe;
  color: #7c3aed;
  padding: 8px 14px;
  border-radius: 999px;
  font-size: 14px;
  font-weight: 700;
  margin-bottom: 18px;
}

.hero h1 {
  font-size: 56px;
  line-height: 1.1;
  margin-bottom: 18px;
  max-width: 580px;
}

.subtitle {
  font-size: 18px;
  color: #6b7280;
  max-width: 520px;
  margin-bottom: 28px;
}

.hero-buttons {
  display: flex;
  gap: 14px;
}

.primary-btn,
.secondary-btn {
  text-decoration: none;
  padding: 14px 22px;
  border-radius: 14px;
  font-weight: 700;
}

.primary-btn {
  background: #7c3aed;
  color: white;
}

.secondary-btn {
  background: white;
  color: #111827;
  border: 1px solid #ddd;
}

.calendar-link {
  text-decoration: none;
  color: inherit;
  display: block;
}

.mockup-card {
  background: white;
  border-radius: 28px;
  padding: 22px;
  box-shadow: 0 12px 40px rgba(124, 58, 237, 0.08);
  border: 1px solid #ece7ff;
}

.mockup-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 18px;
}

.mockup-top button {
  background: #7c3aed;
  color: white;
  border: none;
  border-radius: 12px;
  padding: 10px 14px;
  cursor: pointer;
}

.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 8px;
}

.cell {
  height: 70px;
  background: #fafafa;
  border: 1px solid #eee;
  border-radius: 12px;
  padding: 6px;
}

.event-tag {
  background: #fbcfe8;
  color: #9d174d;
  padding: 4px 8px;
  border-radius: 10px;
  font-size: 12px;
  display: inline-block;
}

.features,
.about {
  max-width: 1200px;
  margin: 0 auto;
  padding: 30px 24px 70px;
}

.section-title {
  text-align: center;
  margin-bottom: 32px;
}

.section-title h2 {
  font-size: 34px;
  margin-bottom: 10px;
}

.section-title p {
  color: #6b7280;
}

.feature-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 22px;
}

.feature-card {
  background: white;
  border-radius: 22px;
  padding: 26px;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04);
}

.feature-card h3 {
  margin-bottom: 10px;
}

.feature-card p {
  color: #6b7280;
}

.about-box {
  background: linear-gradient(135deg, #ede9fe, #fce7f3);
  border-radius: 28px;
  padding: 40px;
}

.footer {
  background: #ffffff;
  border-top: 1px solid #ece7ff;
  padding: 28px 24px;
}

.footer-content {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  gap: 30px;
  flex-wrap: wrap;
}

.footer h3 {
  color: #7c3aed;
  margin-bottom: 8px;
}

.footer p {
  color: #6b7280;
  max-width: 320px;
}

.footer-links {
  display: flex;
  gap: 18px;
  align-items: center;
  flex-wrap: wrap;
}

.footer-links a {
  color: #374151;
  text-decoration: none;
}

.footer-links a:hover {
  color: #7c3aed;
}

@media (max-width: 900px) {
  .hero {
    grid-template-columns: 1fr;
  }

  .hero h1 {
    font-size: 40px;
  }

  .feature-grid {
    grid-template-columns: 1fr;
  }
}
</style>