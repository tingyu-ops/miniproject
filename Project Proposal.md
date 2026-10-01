# Project Proposal: PHP + MySQL Application

> **Course:** J620-002-4:2020 Front-End Software Development (Level 4)
> **Competency Unit:** J620-002-4:2020-C01
> **Instructions:** Replace every `[ ... ]` and delete the hint lines (starting with `>`) before submitting. Keep this file as `README.md` in the root of your project repository.

---

## 1. Student Details

| Field | Your Answer |
|---|---|
| Candidate Name | Ting Yu |
| NRIC Number | 080124-07-0418 |
| Date Submitted | [ ... ] |

---

## 2. Project Title

**Movie Review System**

### One-line summary
A web platform where users can browse, rate, and review movies, while moderators manage movie content and admins oversee the entire system.

---

## 3. Problem Statement & Purpose

Many people find it difficult to choose movies because movie information, ratings, and reviews are often scattered across different platforms. The Movie Review System provides a centralized platform for movie lovers to browse movies, view ratings, and share reviews. It is designed for movie viewers, movie moderator, and administrators to make movie discovery and review management easier and more organized. 

---

## 4. Tech Stack

| Layer | Technology |
|---|---|
| Markup | HTML5 |
| Styling | CSS3 |
| Server-side | PHP |
| Database | MySQL |

---

## 5. Types of Users (Roles)

> Minimum **3 roles**. Each role must have different levels of access.

| Role | Description |
|---|---|
|Admin | Browse movies, search movies, rate movies, write reviews, and manage their watchlist. |
| Movie Moderators | Add and update movie information, manage movie content, and approve or reject reviews. |
| Users | Manage users, moderators, movies, reviews, categories, and overall system settings. |

### Role-Based Access Matrix

> Mark what each role can do. Add or remove rows to match your features.

| Feature / Page | Admin | Movie Moderator | Users | Guest (not logged in) |
|---|:---:|:---:|:---:|:---:|
| Register / Login | ✅ | ✅ | ✅ | ✅ |
|Manage Users | ✅ | ❌ | ❌ | ❌ |
|Manage Movies| ✅ | ✅ |❌ | ❌ |
|Manage Genres | ✅ | ✅ | ❌ | ❌ |
|Browse Movies |✅ | ✅ | ✅ | ✅ |
|Search / Filter Movies	| ✅ | ✅ | ✅	✅ |
|View Movie Details |✅ | ✅ | ✅ | ✅ |
|Rate Movies | ❌ | ❌ | ✅ | ❌ |
|Write Reviews	| ❌ | ❌ | ✅ | ❌ |
|Edit / Delete Own Reviews | ❌ | ❌ | ✅ | ❌ |
|Approve / Reject Reviews | ✅ | ✅ | ❌ | ❌ |
|Manage Reported Reviews | ✅ | ✅ | ❌ | ❌ |
|Add Movies to Watchlist | ❌ | ❌ | ✅ | ❌ |
|View Watchlist	| ❌ | ❌ | ✅ | ❌ |
|View System Statistics	| ✅ | ❌ | ❌ | ❌ |

---

## 6. Features

### 6.1 Core Features (must have)

- [ ] User registration and login
- [ ] Role-based access control (each role sees/does different things)
- [ ] Data management (Create, Read, Update, Delete)
- [ ] Movie search and filtering
- [ ]  Movie rating and review system

### 6.2 Extra Features (nice to have)

- [ ] Watchlist
- [ ] Review reporting system
- [ ] Movie recommendation
- [ ] Dashboard with movie/review statistics


### 6.3 Feature Descriptions

> Briefly explain each core feature: what it does and which role uses it.

| Feature | Description | Role(s) |
|---|---|---|
| User Registration & Login | Users can create accounts and log in according to their role. | All |
| Role-Based Access | Different roles have different permissions and access to features. | All |
| Movie Management | Add, view, edit and delete movie information. | Admin, Movie Moderator |
| Search & Filter | Users can search movies by title and filter them by genre or rating. | All |
| Rating & Review | Users can give ratings and write reviews for movies. | Users |
| Watchlist | Users can save movies they want to watch later. | Users |
| Review Management | Moderators can approve, reject or manage inappropriate reviews. | Admin, Movie Moderator |
| User Management | Admin can manage registered users and their roles. | Admin |

---

## 7. Data Management System

> Which data can users create, view, edit and delete? Who is allowed to do what?

| Data / Entity | Create | Read | Update | Delete |
|---|---|---|---|---|
| Users | Admin/Users | Admin | Admin/User* | Admin |
| Movies | Admin/Moderator | All | Admin/Moderator | Admin/Moderator |
| Genres | Admin/Moderator | All | Admin/Moderator| Admin/Moderator |
| Review | Users| All | Users*/Moderator | Users*/Moderator |
| Rating | Users | All | Users | Users |
| Watchlist | Users | All | Users | Users |
*Users can edit/delete with their own.
---

## 8. Database Design

### Entity Relationship Diagram (ERD)
```mermaid
> erDiagram
    USERS ||--o{ REVIEWS : writes
    USERS ||--o{ RATINGS : gives
    USERS ||--o{ WATCHLIST : saves
    MOVIES ||--o{ REVIEWS : receives
    MOVIES ||--o{ RATINGS : receives
    MOVIES ||--o{ WATCHLIST : included_in
    GENRES ||--o{ MOVIES : contains

    USERS {
        int user_id PK
        string name
        string email
        string password
        string role
    }

    MOVIES {
        int movie_id PK
        int genre_id FK
        string title
        date release_date
        string description
        string poster
    }

    GENRES {
        int genre_id PK
        string genre_name
    }

    REVIEWS {
        int review_id PK
        int user_id FK
        int movie_id FK
        int rating
        string comment
        date review_date
        string review_status
    }

    RATINGS {
        int rating_id PK
        int user_id FK
        int movie_id FK
        int rating
    }

    WATCHLIST {
        int watchlist_id PK
        int user_id FK
        int movie_id FK
        date added_date
    }
---

## 9. Use Case Diagram

```mermaid
flowchart LR
    A([Admin])
    B([Movie Moderator])
    C([User])
    D([Guest])

    A --> UC1[Login]
    B --> UC1
    C --> UC1

    A --> UC2[Manage Users]
    A --> UC3[Manage Movies]
    A --> UC4[Manage Genres]
    A --> UC5[Manage Reviews]
    A --> UC6[View System Statistics]

    B --> UC3
    B --> UC4
    B --> UC5

    C --> UC7[Browse Movies]
    C --> UC8[Search / Filter Movies]
    C --> UC9[Rate Movies]
    C --> UC10[Write Reviews]
    C --> UC11[Manage Watchlist]

    D --> UC7
    D --> UC8
    D --> UC12[View Movie Details]
```

---

## 10. Presentation Checklist

- [ ] Can explain the purpose of the application
- [ ] Can justify design choices (why this database structure, why these roles)
- [ ] Can demo every role
- [ ] Can answer questions about my own code
- [ ] Submitted on time
