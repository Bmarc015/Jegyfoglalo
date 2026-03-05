const TEAM_LOGO_ALIASES = {
  "real madrid": "real madrid.png",
  "fc barcelona": "barka.webp",
  "manchester city": "city.png",
  "liverpool fc": "pool.png",
  "bayern munchen": "bayern.png",
  "paris saint germain": "psg.png",
  "arsenal fc": "arsenal.svg",
  "inter milan": "inter.png",
  "juventus": "juventus.svg",
  "atletico madrid": "atma.png",
  "borussia dortmund": "bvb.png",
  "bayer leverkusen": "bayern kusen.png",
  "tottenham hotspur": "tottenham.png",
  "manchester united": "united.png",
  "rb leipzig": "leipzih.png",
  "sporting cp": "sportin.png",
  "feyenoord": "feye.png",
  "olympique marseille": "marseille.png",
  "as monaco": "monaco.png",
  "lille osc": "lille.png",
  "lazio": "laziooo.png",
  "fiorentina": "fio.png",
  "sevilla fc": "sevilla.png",
  "real betis": "betis.png",
  "real sociedad": "sociedad.png",
  "villarreal cf": "villarreal.png",
  "athletic bilbao": "bilbao.png",
  "newcastle united": "newcastle.svg",
  "brighton hove albion": "brighton.png",
  "west ham united": "west ham.png",
  "eintracht frankfurt": "frnakfurt.png",
  "vfb stuttgart": "stuttgart.png",
  "sc freiburg": "freiburg.png",
  "vfl wolfsburg": "wolfsburg.png",
  "fenerbahce": "fener.png",
  "galatasaray": "galata.png",
  "besiktas": "besiktas.png",
  "panathinaikos": "panathi.png",
  "paok": "paok.jpg",
  "celtic fc": "celtic.png",
  "rangers fc": "rangers.png",
  "club brugge": "clubb brugge.svg",
  "anderlecht": "anderl.png",
  "red bull salzburg": "salzburgi.png",
  "sturm graz": "strum.png",
  "dinamo zagreb": "dinamo.png",
  "hajduk split": "hajduk split.png",
  "slavia praha": "slavia praha.svg",
  "sparta praha": "sparta praha.svg",
  "ferencvaros": "ftc.png",
  "mol fehervar": "mol fehervar.png",
  "debreceni vsc": "dvsc.png",
  "puskas akademia": "puskas.png",
  "mtk budapest": "mtk budapest.png",
  "ujpest fc": "ujpest.svg",
  "boca juniors": "boca.png",
  "river plate": "river plate.png",
  "flamengo": "flamengo.png",
  "palmeiras": "palmeiras.png",
  "sao paulo fc": "sao paulo.png",
  "corinthians": "corinthia.png",
  "santos": "neymar.png",
  "inter miami": "messi.png",
  "la galaxy": "la galaxy.png",
  "al hilal": "al hilal.svg",
  "al nassr": "ronaldo aura.png",
  "al ittihad": "ittihad.png",
  "al ahli": "al ahli.svg",
  "shakhtar donetsk": "shaktar.png",
  "dynamo kyiv": "dynamo.svg",
  "fc basel": "basel.png",
  "young boys": "young b.png",
  "sheriff tiraspol": "sheriff.svg",
  "ludogorets": "ludooo.png",
  "crvena zvezda": "crvena.png",
  "partizan": "partizan.svg",
  "malmo ff": "malmo.png",
  "fc copenhagen": "koppenhaga.png",
  "fc midtjylland": "midtjylland.png",
  "bodo glimt": "bodo.png",
  "maccabi haifa": "maccabi.png",
  "aek athens": "athen.png",
  "girona fc": "girona.png",
  "ogc nice": "nice.png",
  "rc lens": "lens.png",
  "stade rennais": "rennes.png",
  "tsg hoffenheim": "hoffeinheim.png",
  "valencia cf": "valencia.png",
  "ac milan": "https://upload.wikimedia.org/wikipedia/commons/d/d0/Logo_of_AC_Milan.svg",
  "ajax": "ajax.svg",
  "psv eindhoven": "psv.png",
  "atalanta": "atalanta.png",
  "benfica": "benfing.png",
  "napoli": "napoli.png",
  "as roma": "roma.png",
  "chelsea fc": "chelsea.png",
  "aston villa": "aston.png",
};

const TEAM_LOGO_KEYWORDS = {
  ajax: "ajax.svg",
  arsenal: "arsenal.svg",
  barcelona: "barka.webp",
  bayern: "bayern.png",
  dortmund: "bvb.png",
  brighton: "brighton.png",
  chelsea: "chelsea.png",
  copenhagen: "koppenhaga.png",
  fenerbahce: "fener.png",
  frankfurt: "frnakfurt.png",
  freiburg: "freiburg.png",
  galatasaray: "galata.png",
  girona: "girona.png",
  hoffenheim: "hoffeinheim.png",
  juventus: "juventus.svg",
  lazio: "laziooo.png",
  leipzig: "leipzih.png",
  liverpool: "pool.png",
  manchester: "city.png",
  marseille: "marseille.png",
  milan: "https://upload.wikimedia.org/wikipedia/commons/d/d0/Logo_of_AC_Milan.svg",
  monaco: "monaco.png",
  napoli: "napoli.png",
  newcastle: "newcastle.svg",
  olympiacos: "olympiacos.png",
  panathinaikos: "panathi.png",
  psg: "psg.png",
  rangers: "rangers.png",
  sevilla: "sevilla.png",
  sociedad: "sociedad.png",
  sporting: "sportin.png",
  stuttgart: "stuttgart.png",
  tottenham: "tottenham.png",
  valencia: "valencia.png",
  villarreal: "villarreal.png",
  wolfsburg: "wolfsburg.png",
};

function normalizeTeamKey(value) {
  if (!value) return "";
  return String(value)
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .replace(/&/g, " and ")
    .replace(/[^a-z0-9 ]+/g, " ")
    .replace(/\s+/g, " ")
    .trim();
}

export function resolveTeamLogo(teamName) {
  const teamKey = normalizeTeamKey(teamName);
  if (!teamKey) return "";

  const aliasFileName = TEAM_LOGO_ALIASES[teamKey];
  if (aliasFileName?.startsWith("http")) return aliasFileName;
  if (aliasFileName) {
    return `/csapat%20kepek/${encodeURIComponent(aliasFileName)}`;
  }

  for (const [keyword, filename] of Object.entries(TEAM_LOGO_KEYWORDS)) {
    if (teamKey.includes(keyword)) {
      if (filename.startsWith("http")) return filename;
      return `/csapat%20kepek/${encodeURIComponent(filename)}`;
    }
  }

  return "";
}

