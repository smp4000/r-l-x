# Changelog

Alle wichtigen Änderungen am Projekt werden in dieser Datei dokumentiert.

Das Format basiert auf [Keep a Changelog](https://keepachangelog.com/de/1.0.0/) und dieses Projekt folgt [Semantic Versioning](https://semver.org/lang/de/).

---

## [Unreleased]

### 🐛 Behoben

**Filament 5 Kompatibilität:**
- 🔧 WatchResource vollständig implementiert mit 5-Tab-Interface (Grunddaten, Preise & Marktdaten, Technische Details, Dokumentation, Notizen & Historie)
- 🔧 Alle Layout-Komponenten (Section, Tabs) korrekt zu `Filament\Schemas\Components` migriert
- 🔧 Actions-Namespace von `Filament\Tables\Actions` zu `Filament\Actions` aktualisiert
- 🔧 Dealer-Relationships zu Watch-Model hinzugefügt (`purchaseDealer`, `sellingDealer`) inkl. eigener Migration

**ApiSettingsPage Korrekturen:**
- 🔧 `InteractsWithForms` Trait korrekt implementiert
- 🔧 `form(Schema $schema): Schema` Methodensignatur korrigiert (statt `Form $form`)
- 🔧 Section-Import korrigiert: `Filament\Schemas\Components\Section` (nicht `Forms\Components`)
- 🔧 Blade-Template vereinfacht: Direkter `<x-filament::button>` statt nicht-existierender `form.actions` Komponente
- 🔧 Standard Form-Pattern mit `$this->form->fill()` und `$this->form->getState()`

---

## [0.1.0] - 2026-02-11

### 🎉 Initiales Release - Foundation System

#### ✨ Hinzugefügt

**Basis-System:**
- ✅ Laravel 11 mit Filament 5 Setup
- ✅ Multi-Tenant Benutzerverwaltung
- ✅ Admin-Rollen und Aktivierungs-Status für User
- ✅ Deutsche Lokalisierung (UI-Texte)

**Marken-Verwaltung:**
- ✅ Marken CRUD (Create, Read, Update, Delete)
- ✅ Logo-Upload für Marken
- ✅ Herkunftsland und Gründungsjahr
- ✅ Aktiv/Inaktiv-Status
- ✅ Soft Deletes (Papierkorb)

**Kontakte/CRM (Dealer):**
- ✅ Kontakt-Verwaltung für Händler, Juweliere und Privatpersonen
- ✅ Vollständige Adressverwaltung
- ✅ Käufer/Verkäufer-Flags
- ✅ Tags und Notizen für CRM
- ✅ Kauf- und Verkaufshistorie (Relationen zu Uhren)

**Uhren-Verwaltung (HAUPTFEATURE):**
- ✅ Umfassende Uhrenverwaltung mit Status (Besitz, Wunschliste, Verkauft)
- ✅ Technische Details:
  - Gehäuse (Material, Durchmesser, Höhe, Lünette, Glas, Wasserdichtigkeit)
  - Zifferblatt (Farbe, Zahlen)
  - Armband (Material, Farbe, Schließe)
  - Uhrwerk (Aufzug, Kaliber, Gangreserve, Steine, Frequenz)
  - Funktionen (Array/Tags)
  - Geschlecht
- ✅ Kaufdetails (Preis, Datum, Ort, Zustand, Box, Papiere)
- ✅ Verkaufsdetails (Datum, Preis, Käufer, Notizen)
- ✅ Versicherungsdaten (Gesellschaft, Police, Wert, Gültigkeit)
- ✅ Marktwert-Tracking
- ✅ Limitierte Editionen (Nummer/Gesamt)
- ✅ Aufbewahrungsort und abweichender Eigentümer
- ✅ SoftDeletes

**Bildverwaltung:**
- ✅ Multi-Image-Upload pro Uhr (5-30 Bilder)
- ✅ Hauptbild-Auswahl
- ✅ Bildquellen-Tracking (User-Upload, Hersteller, AI)
- ✅ Automatischer Bild-Download von AI-Quellen
- ✅ Google Custom Search Integration
- ✅ Web-Scraping von Perplexity Source-URLs

**KI-gestützte Datenabfrage:**
- ✅ **Perplexity AI Integration** (mit aktuellem Web-Zugriff)
  - Automatische Erkennung von Marke basierend auf Referenznummer
  - Technische Daten automatisch abrufen
  - Bilder aus Web-Suche oder Source-URLs extrahieren
- ✅ **OpenAI GPT-4o Fallback** (Training-Daten)
- ✅ Prompt-Engineering für präzise JSON-Antworten
- ✅ Rohdaten-Speicherung für Debugging
- ✅ Multi-Provider-Support (User kann wählen)

**Marktwert-Ermittlung:**
- ✅ AI-gestützte Preisermittlung via Perplexity
- ✅ Durchsucht Chrono24, WatchCharts und andere Marktplätze
- ✅ Intelligente Preisberechnung:
  - Median, Durchschnitt, Min/Max
  - Ausreißer-Entfernung (IQR-Methode)
  - Zustandsfaktoren (neu: 1.0, ungetragen: 0.95, getragen: 0.9, stark: 0.75)
  - **Marktwert = Höchstpreis × Zustandsfaktor**
- ✅ Bewertungs-Historie mit vollständigen Details
- ✅ Market Research Logs für Debugging

**API-Verwaltung:**
- ✅ User-spezifische API-Einstellungen
- ✅ Verschlüsselte Speicherung aller API-Keys
- ✅ Filament-Seite für API-Konfiguration
- ✅ Support für:
  - Perplexity AI API Key
  - OpenAI API Key
  - Google Custom Search API Key + Engine ID

**Filament Admin-Panel:**
- ✅ Responsive Tabellen mit erweiterten Filtern
- ✅ Deutsche UI-Texte überall
- ✅ Hover-Zoom für Bilder in Tabellen
- ✅ Badge-Status für Besitz/Wunschliste
- ✅ Inline-Actions (Daten abrufen, Wert ermitteln)
- ✅ Modal-Dialoge für Historie und Logs
- ✅ Formular-Tabs für übersichtliche Darstellung
- ✅ Progress-Badges ("5/8 ausgefüllt")

**Datenbank:**
- ✅ 8 strukturierte Tabellen mit Relationen
- ✅ Foreign Keys mit CASCADE/RESTRICT
- ✅ Indizes für Performance
- ✅ SoftDeletes wo sinnvoll
- ✅ JSON-Felder für flexible Datenstrukturen
- ✅ Verschlüsselte Cast für sensible Daten

**Services & Business-Logik:**
- ✅ `PerplexityWatchFetcher` - Web-Suche mit AI
- ✅ `WatchDataFetcher` - OpenAI Fallback
- ✅ `ImageDownloader` - Google Search + Web Scraping
- ✅ `MarketValueCalculator` - Intelligente Preisberechnung
- ✅ Umfangreiches Logging für Debugging

---

### 📚 Dokumentation

- ✅ Vollständige deutsche Kommentare in allen Dateien
- ✅ Docblocks für alle Models und Services
- ✅ Inline-Erklärungen in Migrations

---

### 🏗️ Technischer Stack

- **Backend:** Laravel 11
- **Admin-Panel:** Filament 5
- **Datenbank:** MySQL
- **AI-Provider:** Perplexity AI, OpenAI GPT-4o
- **Bildsuche:** Google Custom Search
- **Storage:** Laravel Storage (local/public)
- **Caching:** Laravel Cache
- **Verschlüsselung:** Laravel Encrypted Casts

---

### 🎯 Status: Production Ready (Phase 1)

**Was funktioniert:**
- ✅ Komplette Uhrenverwaltung (Besitz, Wunschliste)
- ✅ Automatische Datenbeschaffung via AI
- ✅ Marktwert-Ermittlung mit Historie
- ✅ Bildverwaltung mit AI-Download
- ✅ CRM für Kontakte
- ✅ Multi-User-fähig

**Was kommt in Phase 2 (v0.2.0):**
- ⏳ Status "Verkauft" mit vollständigen Details
- ⏳ Dokumente-Upload (Garantie, Rechnung, Serviceheft)
- ⏳ Service-Management (Wartung, Reparaturen)
- ⏳ Service-Erinnerungen per E-Mail
- ⏳ Dashboard-Widgets

---

### 🙏 Credits

- **Framework:** Laravel (Taylor Otwell)
- **Admin-Panel:** Filament (Dan Harrin)
- **AI-Provider:** Perplexity AI, OpenAI
- **Entwickler:** [Ihr Name]

---

[Unreleased]: https://github.com/smp4000/r-l-x/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/smp4000/r-l-x/releases/tag/v0.1.0
