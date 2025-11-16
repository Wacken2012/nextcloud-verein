# 🌳 Git Branch Strategy

Branch-Workflow für die Nextcloud Vereins-App.

---

## 📋 Übersicht

```
                    RELEASES (Tags)
                         ↑
                         │
    main ───────────────────────────── (v0.1.0, v0.2.0, v1.0.0)
      ↑
      │ merge nach erfolgreichem Test
      │
    develop ───────────────────────── (v0.2.0-beta, v0.3.0-beta)
      ↑                ↑
      │                │ feature branches
      │          ┌─────┴─────┬──────────┬──────────┐
      │          │           │          │          │
    feature/rbac feature/pdf feature/sepa feature/
              export        export       error-handling
```

---

## 🎯 Branch-Rollen

### `main` Branch
**Status**: ✅ **Production - Stabil**

| Eigenschaft | Beschreibung |
|-------------|-------------|
| **Version** | v0.1.0 (aktuell stabil) |
| **Status** | ✅ Production-Ready |
| **Basis für** | Releases & Hotfixes |
| **Merges von** | `develop` (nach v0.2.0 Test) |
| **Hotfixes** | `bugfix/*` branchen |

**Releases auf `main`**:
- v0.1.0 ✅ (aktuell - Nov 2025)
- v0.2.0 🔧 (geplant - Dez 2025)
- v1.0.0 📋 (geplant - 2026)

### `develop` Branch
**Status**: 🔧 **In Entwicklung - Beta**

| Eigenschaft | Beschreibung |
|-------------|-------------|
| **Version** | v0.2.0-beta (aktuell) |
| **Status** | 🔧 Development/Beta |
| **Basis für** | Features & Improvements |
| **Merges zu** | `main` nach Release |
| **Pull Requests** | ⬅️ **ALLE PRs öffnen gegen develop!** |

**Beta Releases auf `develop`**:
- v0.2.0-beta (geplant - Anfang Dez)
- v0.2.0-rc1 (geplant - Mitte Dez)
- v0.2.0-rc2 (falls nötig - Ende Dez)

### Feature Branches
**Pattern**: `feature/[feature-name]`

| Eigenschaft | Beschreibung |
|-------------|-------------|
| **Basierend auf** | `develop` |
| **Merges zu** | `develop` (via PR) |
| **Beispiele** | `feature/rbac`, `feature/pdf-export` |
| **Lifetime** | Solange wie Feature entwickelt wird |

### Bugfix Branches
**Pattern**: `bugfix/[bug-name]` oder `fix/[issue-number]`

| Eigenschaft | Beschreibung |
|-------------|-------------|
| **Basierend auf** | `develop` (für neue Bugs) oder `main` (für Production-Bugs) |
| **Merges zu** | `develop` (via PR) |
| **Beispiele** | `bugfix/dark-mode-colors`, `fix/issue-42` |
| **Lifetime** | Bis Bug gefixt ist |

---

## 🔄 Workflow-Beispiele

### Szenario 1: Neues Feature entwickeln

```bash
# 1. develop aktualisieren
git checkout develop
git pull origin develop

# 2. Feature-Branch erstellen
git checkout -b feature/rbac

# 3. Development durchführen
# ... code changes ...

# 4. Commits mit Conventional Commits
git commit -m "feat: implement admin role check"
git commit -m "test: add RBAC unit tests"
git commit -m "docs: update DEVELOPMENT.md with RBAC patterns"

# 5. Branch in Sync halten
git fetch origin
git rebase origin/develop

# 6. Zu GitHub pushen
git push origin feature/rbac

# 7. Pull Request öffnen
# → Target: develop (NOT main!)
# → Title: "feat: Add RBAC tests, ValidationService, and comprehensive documentation"
# → Description: Ausführliche Erklärung + Testing Info

# 8. Nach PR-Approval mergen
# → Im GitHub PR "Squash and merge" oder "Create a merge commit"
```

### Szenario 2: Bugfix in Production

```bash
# 1. main auschecken (für Production-Bugs)
git checkout main
git pull origin main

# 2. Bugfix-Branch erstellen
git checkout -b fix/dark-mode-colors

# 3. Bug fixieren
# ... code changes ...

# 4. Commit
git commit -m "fix: dark mode colors not applying in Safari"

# 5. Tests durchführen
npm run test
npm run build

# 6. PR gegen main öffnen
git push origin fix/dark-mode-colors
# → Target: main
# → Beschreibung: Welcher Bug, wie gelöst, getestet

# 7. Nach Merge: develop aktualisieren
git checkout develop
git pull origin develop
git merge main
git push origin develop
```

### Szenario 3: v0.2.0 Release vorbereiten

```bash
# 1. Release-Checklist durch
# ✅ Alle Features in develop gemergt?
# ✅ Alle Tests grün?
# ✅ Dokumentation aktuell?
# ✅ README & CHANGELOG aktualisiert?

# 2. Beta-Tag erstellen
git tag -a v0.2.0-beta -m "v0.2.0 Beta Release"
git push origin v0.2.0-beta

# 3. Nach erfolgreichem Test: Release-Tag
git tag -a v0.2.0 -m "v0.2.0 Production Release"
git push origin v0.2.0

# 4. develop in main mergen
git checkout main
git pull origin main
git merge develop
git push origin main

# 5. Neuen develop für nächste Phase erstellen (optional)
git checkout -b develop-next
git push origin develop-next
```

---

## 📝 Commit-Message-Format

### Conventional Commits

**Format**: `<type>(<scope>): <subject>`

```
feat(member): add IBAN validation
fix(finance): correct fee calculation
docs(readme): update branch strategy
test(rbac): add admin role tests
chore(build): update dependencies
```

**Types**:
- `feat`: Neue Funktionalität
- `fix`: Bugfix
- `docs`: Dokumentation
- `test`: Tests hinzufügen/ändern
- `refactor`: Code-Umstrukturierung
- `perf`: Performance-Verbesserung
- `chore`: Build, Dependencies, Tooling
- `ci`: CI/CD Konfiguration
- `style`: Code-Style (Whitespace, Formatierung)

**Scopes** (für diese App):
- `member`: Mitgliederverwaltung
- `finance`: Finanzverwaltung
- `rbac`: Rollen & Berechtigungen
- `validation`: Validierungslogik
- `alert`: Alert-Komponente
- `build`: Build-System
- `tests`: Test-Infrastruktur
- `docs`: Dokumentation
- `readme`: README
- `api`: API Endpoints
- `ui`: User Interface
- `dark-mode`: Dark-Mode Support

**Beispiele**:

```bash
git commit -m "feat(member): add IBAN Mod-97 checksum validation"

git commit -m "fix(finance): correct fee status transition logic"

git commit -m "test(rbac): add Treasurer role access tests"

git commit -m "docs(development): add RBAC test patterns (section 8)"

git commit -m "chore: setup develop branch for v0.2.0 feature development"
```

---

## ✅ Release-Prozess

### v0.2.0 Release (geplant Dezember 2025)

**Phase 1: Feature-Entwicklung (aktuell - bis 1. Dezember)**
```bash
# Features in develop mergen
# - RBAC (Role-Based Access Control)
# - ValidationService (Email, IBAN, Phone)
# - PDF-Export
# - SEPA-Export
# - Verbessertes Error Handling
```

**Phase 2: Beta-Testing (1.-15. Dezember)**
```bash
# Tag v0.2.0-beta erstellen
git tag -a v0.2.0-beta -m "v0.2.0 Beta"
git push origin v0.2.0-beta

# Community-Feedback sammeln
# Bugs fixieren in `develop`
```

**Phase 3: Release-Kandidaten (15.-30. Dezember)**
```bash
# Release-Kandidaten nach Bedarf
git tag -a v0.2.0-rc1 -m "v0.2.0 Release Candidate 1"
git tag -a v0.2.0-rc2 -m "v0.2.0 Release Candidate 2"
```

**Phase 4: Finales Release (Ende Dezember)**
```bash
# Final Release Tag
git tag -a v0.2.0 -m "v0.2.0 Release"
git push origin v0.2.0

# develop in main mergen
git checkout main
git merge develop
git push origin main
```

---

## 🔐 Branch Protection Rules

**Empfohlene GitHub Settings**:

### `main` Branch
- ✅ Require pull request reviews before merging (2 reviews)
- ✅ Dismiss stale pull request approvals
- ✅ Require status checks to pass (Build, Tests)
- ✅ Require branches to be up to date before merging
- ✅ Restrict who can push (nur maintainers)
- ✅ Require signed commits

### `develop` Branch
- ✅ Require pull request reviews before merging (1 review)
- ✅ Require status checks to pass (Build, Tests)
- ✅ Allow force pushes? ❌ NO
- ✅ Require branches to be up to date before merging

---

## 📊 Branch-Übersicht

```bash
# Alle lokalen Branches anzeigen
git branch -a

# Remote-Branches anzeigen
git branch -r

# Branch-Info
git branch -vv

# Branches nach letztem Commit sortiert
git branch --sort=-committerdate
```

**Erwartete Branches**:
```
* develop                           → v0.2.0-beta (aktuelle Entwicklung)
  main                             → v0.1.0 (Production)
  origin/develop                   → Remote develop
  origin/main                      → Remote main
  origin/feature/rbac              → Feature (wenn in Entwicklung)
  origin/feature/pdf-export        → Feature (wenn in Entwicklung)
```

---

## 🚀 Best Practices

### ✅ DO:
- ✅ Feature-Branches immer von `develop` erstellen
- ✅ Regelmäßig gegen `develop` rebasen (um Konflikte zu vermeiden)
- ✅ Aussagekräftige Branch-Namen verwenden
- ✅ Aussagekräftige Commit-Messages schreiben
- ✅ Tests vor Push durchführen
- ✅ PRs mit Beschreibung öffnen
- ✅ Code-Review durchlaufen lassen

### ❌ DON'T:
- ❌ Niemals direkt auf `main` committen
- ❌ Niemals Feature-Branches gegen `main` mergen
- ❌ Nicht `main` in `develop` pullen (nur umgekehrt!)
- ❌ Nicht force-push auf `main` oder `develop`
- ❌ Nicht mit `--force` rebasen auf shared branches
- ❌ Nicht Commits direkt vor PR squashen (PR-Tool machen lassen)

---

## 📚 Zusätzliche Ressourcen

- [Git Branching Model](https://nvie.com/posts/a-successful-git-branching-model/)
- [Conventional Commits](https://www.conventionalcommits.org/)
- [GitHub Flow](https://guides.github.com/introduction/flow/)
- [Semantic Versioning](https://semver.org/)

---

**Status**: ✅ Aktiv ab v0.2.0 Entwicklung (November 2025)  
**Letzte Aktualisierung**: 16. November 2025
