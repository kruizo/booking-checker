


Booking Conflict Checker – Full-Stack
## Laravel & Vue Skills Test
## Goal
Build a Booking Conflict Checker using Laravel 12 (backend) and Vue 3 with Pinia
## (frontend).
This test evaluates:
● Coding structure and standards

● Logic and problem-solving

● Passion for the craft

● Commitment to quality

● Creativity (adding improvements that help users)


## Company Coding Standards
## Laravel Standards
All candidates must follow our internal backend development rules:
● Follow PSR-12 coding standards

● Apply SOLID principles

● Keep controllers thin and clean

● Move business logic into Service classes


● Database operations must be handled in Repository classes

● Use Form Requests for validation

● Use API Resources for consistent responses

● Maintain clean, readable, well-organized code

## Vue Standards
All candidates must follow our internal frontend development rules:
● Use Vue 3 Composition API

● Keep components small and single-responsibility

● Move reusable logic into composables

● Use Pinia for state management

● Follow clean naming, structure, and organization

● Maintain readability and avoid code duplication


Backend Requirements (Laravel 12)
- Booking API
Create a module to accept and analyze bookings.
A Booking contains:
● date

● start_time

● end_time


## Endpoints Required
- Create a booking

- List all bookings

- Conflict report endpoint that returns:

○ Overlapping bookings

○ Conflicting bookings (same date/time)

○ Gaps between bookings

○ Summary in structured JSON


## 2. Conflict Checking Logic
● Implement all logic in a Service class

● Use Repositories to fetch and manage bookings

● Validate requests using Form Requests

● Ensure clean, maintainable code


## 3. Admin
● Admin-only route protected by middleware

● Admin can view all bookings

● Regular users can view only their own bookings



## 4. Scheduled Job
● Daily scheduled task deletes bookings older than 30 days

● Use Laravel’s scheduler


## 5. Creativity Requirement
● Add at least one improvement that would help real-world users

● Improvement must be practical, thoughtful, and well-implemented

● Examples (optional, do not provide to candidates) include: smart suggestions, automatic
sorting, enhanced summaries


Frontend Requirements (Vue 3 + Pinia)
## 1. Authentication
● Build register and login pages

● Use Pinia to manage authentication state

● Protect routes based on authentication

- Booking UI
● List bookings

● Add, edit, delete bookings

● Highlight conflicts, overlaps, and gaps


● Filters and search

● Fully responsive design for mobile, tablet, and desktop

## 3. Admin
● Admin page to view all users’ bookings

- Real-Time Updates
● WebSockets to show real-time booking updates if multiple users are working

## 5. Must Follow Company Coding Standards
● Composition API, composables, Pinia

● Clean and organized components


## What You Must Submit
A GitHub repository containing:
/backend  → Laravel 12 project
/frontend → Vue 3 project with Pinia
README.md → setup, explanations, and improvements

The README must include:
● Setup instructions

● Your thought process

● Your coding decisions


● A complete list of implemented features

● A description of any added improvements


## Evaluation Criteria
Candidates will be assessed on:
- Coding Structure – adherence to PSR-12, SOLID, clean architecture

- Logic – correctness, clarity, conflict detection, edge case handling

- Passion – care, readability, thoughtful design

- Commitment – completeness, attention to detail, project quality

- Creativity / Going Above & Beyond – additional improvements and real-world usability



