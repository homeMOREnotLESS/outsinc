# OUTSINC Pathways

## A Trauma-Informed Case Management Web Application

OUTSINC Pathways is a specialized case management platform designed to streamline outreach and support for vulnerable populations in Cobourg and Northumberland County. Built with a trauma-informed, low-barrier approach, it connects clients through a 60-question smart intake assessment with real-time multi-agency coordination.

---

## 🎯 Core Features

### For Clients
- **Self-Registration Wizard**: Alias/nickname support for privacy and safety
- **60-Question Smart Assessment**: Conditional branching that adapts to each person's situation
- **Personal Dashboard (FOOTPRINT)**: Track goals, complete tasks, earn badges
- **Referral Tracking**: Monitor warm hand-offs with service providers in real-time
- **Resource Library**: Access to local services and support

### For Staff
- **Case Management Dashboard (DCIDE)**: Caseload overview with burnout monitoring
- **Risk Detection**: Automatic identification of Acutely Elevated Risk (AER) situations
- **Hub Discussions**: Multi-agency coordination with Four-Filter privacy model
- **Incident Reporting**: Track and follow up on critical incidents
- **Gamification Tracking**: Monitor client achievements and motivation

### Technical Features
- **Smart Assessment**: Conditional branching (age-gating, need-based skips, escalation)
- **Automated Outcomes**: Auto-generate tasks, goals, referrals, badges
- **Four-Filter Privacy Model**: Multi-level information sharing with built-in compliance
- **Multi-Language Support**: English, French, American Sign Language (ASL)
- **Real-Time Notifications**: SMS, email, in-app alerts
- **Audit Logging**: Complete compliance trail for accountability

---

## 🚀 Quick Start

### Prerequisites
- PHP 8.1 or higher
- MySQL 8.0 or higher
- Composer (optional, for dependency management)
- Node.js 18+ (optional, for Socket.io)

### Installation

1. **Clone the repository**
```bash
cd /workspaces/outsinc
```

2. **Configure environment**
```bash
cd app
cp .env.example .env
# Edit .env with your database credentials and settings
```

3. **Create database**
```bash
mysql -u root -p
> CREATE DATABASE outsinc_pathways;
> CREATE USER 'outsinc'@'localhost' IDENTIFIED BY 'password';
> GRANT ALL PRIVILEGES ON outsinc_pathways.* TO 'outsinc'@'localhost';
> FLUSH PRIVILEGES;
```

4. **Run database migrations**
```bash
php migrations/migration_runner.php
```

5. **Verify with MAMP or local PHP server**
```bash
# Using MAMP Apache (ensure htdocs points to public/ folder)
# OR using PHP built-in server:
php -S localhost:8080 -t public/
```

6. **Access the application**
- Open browser to: `http://localhost:8080`

---

## 📁 Project Structure

```
app/
├── config/              # Configuration & bootstrap
├── core/               # Core utilities (Database, Logger, etc.)
├── models/             # Data models (User, ClientProfile, etc.)
├── services/           # Business logic (Auth, Assessment, Risk Scoring, etc.)
├── controllers/        # Request handlers (not yet created)
├── public/             # Web root
│   ├── index.php       # Application router
│   ├── css/            # Stylesheets
│   ├── js/             # JavaScript
│   ├── pages/          # HTML templates
│   └── api/            # API endpoints
├── views/              # View templates & partials
├── migrations/         # Database migrations
├── data/               # Question & config JSON files
├── tests/              # PHPUnit tests
├── scripts/            # Utility scripts (setup, seeding, etc.)
├── logs/               # Application logs
└── storage/            # Sessions, cache, temp files
```

---

## 📋 The 60-Question Assessment

The assessment is organized into 7 sections:

1. **Connection & Consent** (Q1-5)
   - How they connected, age range, language, consent to coordinate, nickname

2. **Immediate Crisis & Safety** (Q6-15)
   - Housing last night, physical pain, food security, safety, violence, belongings

3. **Housing History & Goals** (Q16-25)
   - Time since housing, episodes, evictions, barriers, ID status, preferences

4. **Mental Health & Wellbeing** (Q26-35)
   - Diagnoses, mental health services, trauma responses, medication, crisis plan

5. **Substance Use & Recovery** (Q36-43)
   - Impact on housing, RAAM/Red Path interest, naloxone access, recovery days

6. **Elderly & Retired Specifics** (Q44-51)
   - *Unlocked if age 60+ selected*
   - Pensions, mobility, senior check-in, medical conditions, meal assistance

7. **Social, Legal & Life Skills** (Q52-60)
   - Doctor, court dates, life skills, legal navigation, trafficking, 30-day goal

**Smart Branching**:
- Under 18 → Youth protection alert
- 60+ → Unlock Section 6
- Q36="No" → Skip rest of Section 5
- Violence/trafficking → Immediate escalation
- Chronic homelessness pattern → Housing pathway goal

---

## 🔐 Four-Filter Privacy Model

Information sharing through four progressive levels (with audit logging):

**Filter 1**: Internal Screening
- Only staff can view full client data (identifiable)

**Filter 2**: De-Identified Hub Discussion
- Multi-agency Hub reviews de-identified summary
- Consensus: Is intervention needed?

**Filter 3**: Limited Identifiers
- If yes: Name + DOB shared to identify agency connections

**Filter 4**: Intervention Planning
- Full collaborative sharing among selected agencies
- Warm hand-off with referral bundle

---

## 🧑‍💼 User Roles

1. **Client** - Self-serve assessment, dashboard, referral tracking
2. **Staff** - Case management, intake review, Hub coordination
3. **Provider** - Agency staff receiving referrals
4. **Admin** - System configuration, user management

---

## 🛠 Development

### Running Tests
```bash
# Unit tests
phpunit tests/unit/

# Integration tests
phpunit tests/integration/

# With coverage report
phpunit --coverage-html=coverage/
```

### Database Migrations
```bash
# Run all pending migrations
php migrations/migration_runner.php --up

# Check migration status
php migrations/status.php
```

### Code Standards
- PSR-12 for PHP code style
- Template: Use Blade-like syntax in views (PHP)
- JavaScript: ES6+ with no build step required (vanilla JS)

---

## 📝 Important Files

| File | Purpose |
|------|---------|
| `config/bootstrap.php` | Application initialization |
| `core/Database.php` | Database abstraction layer |
| `services/AssessmentEngine.php` | 60-question logic & branching |
| `data/questions.json` | All 60 questions & metadata |
| `data/branching-rules.json` | Conditional logic paths |
| `data/outcome-triggers.json` | Auto-generation rules |
| `data/badges.json` | Achievement definitions |
| `models/User.php` | User account management |
| `models/ClientProfile.php` | Client profile & consent |

---

## 🔒 Security

✅ Password hashing: bcrypt (cost: 12)
✅ Account lockout: After 5 failed attempts
✅ SQL injection prevention: Parameterized queries
✅ Session security: HTTP-only cookies, CSRF tokens
✅ Input validation: On all user inputs
✅ Audit logging: Four-Filter compliance tracking
✅ Consent tracking: Complete change history
✅ Data encryption: Sensitive PII fields (optional)

---

## 🐛 Troubleshooting

### Database Connection Fails
1. Verify `.env` has correct credentials
2. Ensure MySQL is running
3. Check database exists: `mysql -u root -p -e "SHOW DATABASES;"`

### Assessment questions not loading
1. Verify `data/questions.json` exists
2. Check AssessmentEngine::loadQuestions() is being called
3. Validate JSON syntax: `php -r 'json_decode(file_get_contents("data/questions.json"));'`

### Headers already sent errors
1. Ensure no output before `header()` calls
2. Check for BOM in PHP files (UTF-8 without BOM)
3. Review whitespace in config files

---

## 📚 Documentation

- `PROGRESS.md` - Implementation status and roadmap
- `migrations/` - Database schema documentation
- `data/` - Question and rule configurations
- Code comments - Follow-along documentation in key services

---

## 🤝 Contributing

1. Create feature branch: `git checkout -b feature/your-feature`
2. Make changes following PSR-12
3. Add tests for new features
4. Commit: `git commit -m "Add feature X"`
5. Push: `git push origin feature/your-feature`

---

## 📞 Support

For questions or issues:
- Check PROGRESS.md for status of specific features
- Review code comments in core services
- Run tests to verify functionality
- Check logs in `app/logs/` directory

---

## 📄 License

This project is built for OUTSINC Pathways and the Cobourg/Northumberland County community.

---

## 🎯 Roadmap

- [x] Database schema & core utilities
- [x] Authentication system
- [x] 60-question assessment engine
- [ ] Risk scoring & AER detection
- [ ] Outcome engine & auto-generation
- [ ] Client & staff dashboards
- [ ] Four-Filter Hub implementation
- [ ] Multi-language support
- [ ] Real-time notifications
- [ ] Comprehensive testing

---

**Status**: Alpha - Foundation complete, ready for service integration
**Last Updated**: 2026-02-21
