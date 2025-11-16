# 🌳 Branch Setup - Quick Reference

**Status:** ✅ Complete | **Date:** November 16, 2025

---

## Quick Start

### Where you are:
```bash
$ git branch -a
  develop          ← You are here (v0.2.0-beta)
* main             ← Production (v0.1.0)
```

### What you need to know:

| Branch | Purpose | Status | When to use |
|--------|---------|--------|------------|
| **main** | Production | ✅ Stable | Bug fixes, patches, releases |
| **develop** | Features | 🔧 Beta | New features, improvements |

---

## 🚀 Start Developing

### 1. Create Feature Branch (from develop)
```bash
git checkout develop
git pull origin develop
git checkout -b feature/your-feature-name
```

### 2. Write Code + Commit
```bash
git commit -m "feat(scope): description"
git commit -m "test(scope): add tests"
```

### 3. Push & Open PR (Target: develop!)
```bash
git push origin feature/your-feature-name
# → Open PR on GitHub
# → Target branch: develop (NOT main!)
# → Add description & testing info
```

### 4. After Approval
```bash
# Merge via GitHub (Squash and merge)
git checkout develop
git pull origin develop
```

---

## 📝 Commit Format

**Conventional Commits:**
```
feat(member): add IBAN validation
fix(finance): correct fee calculation
test(rbac): add permission tests
docs(development): add section 8
chore: setup develop branch
```

**Rules:**
- Type: feat, fix, test, docs, chore, refactor, perf, style, ci
- Scope: member, finance, rbac, validation, alert, etc.
- Subject: Lowercase, imperative, no period

---

## 🔄 Workflow

```
┌─ feature/rbac
│  └─ PR to develop
│     └─ Merge ✅
│        └─ develop updated
│
┌─ main (v0.1.0 - Stable)
│  ↑
└─ develop (v0.2.0-beta - Features)
   ├─ feature/pdf-export
   ├─ feature/sepa-export
   └─ feature/error-handling
```

**New Features go to `develop`!**  
**Never commit directly to `main`!**

---

## 📅 Release Timeline

| Phase | Date | Tag | Status |
|-------|------|-----|--------|
| Feature Dev | Nov 16 - Dec 1 | - | 🔧 Active |
| Beta Testing | Dec 1-15 | v0.2.0-beta | 📋 Planned |
| Release Candidate | Dec 15-25 | v0.2.0-rc1/rc2 | 📋 Planned |
| Production Release | Dec 25+ | v0.2.0 | 📋 Planned |

---

## 📚 Documentation

- **README.md** - Branch overview
- **CONTRIBUTING.md** - Workflow (updated!)
- **BRANCH_STRATEGY.md** - Complete guide
- **DEVELOPMENT.md** - RBAC patterns (sections 8-9)

---

## ✅ Checklist for PRs

Before opening a PR:
- [ ] Branch is based on `develop` (not main!)
- [ ] Code follows PSR-12 (PHP) / Vue style
- [ ] Tests written (80%+ coverage)
- [ ] Documentation updated
- [ ] Commit messages are Conventional Commits
- [ ] `npm run build` passes (0 errors)
- [ ] `npm run test` passes
- [ ] No console warnings

PR Requirements:
- [ ] Title is descriptive (feat:, fix:, etc.)
- [ ] Description includes: What, Why, How
- [ ] Target is `develop` (NOT main!)
- [ ] Linked to any related issues

---

## 🆘 Common Commands

```bash
# Update develop locally
git checkout develop
git pull origin develop

# Create feature branch
git checkout -b feature/my-feature

# View branches
git branch -a

# Push feature branch
git push -u origin feature/my-feature

# Fetch updates
git fetch origin

# Rebase on develop (stay in sync)
git rebase origin/develop

# Check what's different
git diff develop
```

---

## 🔗 Links

- **Repository:** https://github.com/Wacken2012/nextcloud-verein
- **Branches:** https://github.com/Wacken2012/nextcloud-verein/branches
- **Pull Requests:** https://github.com/Wacken2012/nextcloud-verein/pulls
- **Issues:** https://github.com/Wacken2012/nextcloud-verein/issues

---

**Questions?** See `BRANCH_STRATEGY.md` for complete reference or open an issue!
