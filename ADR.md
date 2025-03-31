
Skip to content
DEV Community
Powered by
Algolia
Log in
Create account
Cover image for How Senior Software Engineers Document Their Project
Mangabo Kolawole
Mangabo Kolawole Subscriber

Posted on Oct 27 • Updated on Oct 29
575 14 12 8 12
How Senior Software Engineers Document Their Project
#productivity
#career
#learning
#documentation

There’s one task that software engineers hate, yet this small attention to detail is what separates a good software engineer from a bad one: How do they document their project?📝

A few years ago, I was responsible for setting up a fintech project. Because we decided to move quickly, planning for scalability wasn’t a priority. Our focus was on validating the idea, so we pushed forward, creating APIs, architectures, and systems with simple solutions, not overly concerned about the future.

However, as the person in charge of the backend and infrastructure, I knew that while my memory was reliable, it wouldn’t be enough to recall all the details six months down the line.

In my research, I discovered a convention I liked: ADR, or Architectural Decision Record.

ADR of the fintech API

It’s essentially a document that traces all changes made to an architecture: the change itself, its impact, and what we learned from it.

Think of it as a personal journal but for the team.

If you are interested in more content covering topics like this, subscribe to my newsletter for regular updates on software programming, architecture, technical writing, and tech-related insights.
Why is it important?

    Humans forget: Documenting changes helps us because we easily forget the reasons behind choosing one architecture over another.

    It makes the team better: Let’s say you’ve tried various solutions for an issue and documented the successes and failures. You learn from it, and others can too, even developers who come in after you.

    Future developers will thank you: Imagine a dev coming to a codebase and trying to understand why a change was made five years ago. Somewhere, a developer is likely struggling with this because the previous engineer left without documenting it, and they’re not thrilled. Meanwhile, in another company, a developer finds an ADR explaining those changes, and they’re incredibly grateful.

How do you write one then?

There are several conventions to follow, but you can always adapt them to what works best for you.

The convention that inspired me is here: https://adr.github.io/madr/. You can also check Amazon’s ADR process here: https://docs.aws.amazon.com/prescriptive-guidance/latest/architectural-decision-records/adr-process.html.

Here is an example of a template you can use.

# Example Title: Database Choice for User Data

## Context and Problem Statement

We need a scalable database to store and manage user data efficiently as our user base grows.

## Decision Drivers

* Scalability
* Data consistency
* Ease of integration with existing services

## Considered Options

* PostgreSQL
* MongoDB
* Amazon DynamoDB

## Decision Outcome

Chosen option: **PostgreSQL** because it provides strong data consistency and aligns well with our need for complex queries.

### Consequences

* **Good:** Supports ACID compliance, enhancing data reliability.
* **Bad:** May require more tuning to achieve high performance with large datasets.

### Confirmation

We’ll confirm this decision through periodic load tests and performance reviews as the user base scales.

## Pros and Cons of the Options

### PostgreSQL

* **Good:** ACID compliance, robust community support.
* **Neutral:** Setup and tuning can be time-consuming.
* **Bad:** Lacks native horizontal scaling.

### MongoDB

* **Good:** Schema flexibility, horizontal scaling.
* **Bad:** No ACID compliance across collections, limiting data integrity.

## More Information

For additional details, see the database performance evaluation [here](link-to-evaluation).

This kind of document can be present within the project repository, or a notion, or JIRA.

In my last company, where I worked as a frontend engineer, we didn’t have a single document for all architectural changes.

Using GitLab issues and linking every change to an issue branch helped us track the reasons behind changes, even months after implementation.

This practice saved us countless times. As I always say, no matter how smart you or your teammates are—your CTO, manager, or anyone involved in the project—they won’t remember every technical decision made two years ago.

Unless, of course, you’re working with 10x engineers. 😆

Conclusion

And that’s it for this article. We have discussed how companies and tech team leaders use ADRs to document architectural decisions on their projects and how much it helps them, teammates, or even the people who worked there after they left.

If you have experiences to share or any thoughts on the article, feel free to drop them in the comments below.

I’m always open to feedback and happy to engage in discussions that can help us all learn and grow.

If you enjoyed this article and want more insights like this, subscribe to my newsletter for weekly tips, tutorials, and stories delivered straight to your inbox!
profile
Highlight
Promoted

Image of Highlight
Find Code Bottlenecks Fast 🏎️

No more hunting through endless lines of code. With Highlight.io, you can spot performance bottlenecks instantly, from frontend to backend.

Optimize your app, improve load times, and keep users happy – all with a few clicks.

Start diagnosing smarter with Highlight.io.

Get Started
Top comments (22)
Subscribe
pic
 
johndotowl profile image
•
Oct 28

Hey Chatgpt...
Reply
 
prahladyeri profile image
•
Oct 28

Hello Gemini..
Reply
 
fast profile image
•
Oct 30

Wow you're using Gemini???
Reply
 
koladev profile image
•
Oct 28

haha ChatGPT is goated for those tasks
Reply
 
fast profile image
•
Oct 30

Claude >>>
Reply
 
wadecodez profile image
•
Oct 28

👋 You're welcome
Reply
 
navneet_verma profile image
•
Oct 27

Really needed this as documentation is something every dev is worried about! can you create a detailed guide on this tho?
Reply
 
koladev profile image
•
Oct 28

Sure I will do
Reply
 
kmaheshbabu profile image
•
Oct 30

This helpful article for every dev, thanks.
Reply
 
aldycool profile image
•
Oct 30

Whole-heartedly agree with this. Sadly, this is mostly ignored by the majorities. They will only realize that THEY need this when they're older and have to deal with many projects...
Reply
 
dhan profile image
•
Nov 2

Mistral AI
Reply
 
tn_decor profile image
•
Oct 31

chat GPT nè
Reply
 
jairo-dev-jr profile image
•
Oct 31

Here in my team we adopted use RFC model, in every initiative or new software, to document, all of the fluxes.
Reply
 
teminian profile image
•
Oct 30

What a clever idea. Impressed. Thanks for sharing your secret weapon. ;)
Reply
 
asologor profile image
•
Oct 29

Use sequence diagrams.
Reply
 
koladev profile image
•
Oct 29

Yup. this is a tool that should be part of the ADR. I've done that before. Very useful.
Reply
 
katafrakt profile image
•
Nov 3

How would you use sequence diagrams to document something like choice of a database? 🤔
Reply
 
samuel_dossantos_3d13069 profile image
•
Nov 5

Good article. Thank you for sharing your thoughts.
Reply
View full discussion (22 comments)
Code of Conduct • Report abuse
profile
Stellar Development Foundation
Promoted
Migrate from EVM to Rust

Migrate from EVM

Migrate your smart contracts from Solidity to Rust. Let us know how we can help.

Make the Move
Read next
aravindmetquay profile image
What Makes a Great Hacker?

Aravind Roy - Nov 6
hax profile image
TryHackMe API Wizard Breach Walkthrough

haXarubiX - Nov 5
xavier2code profile image
Daliy.rust day 3

xavier2code - Nov 5
epklein profile image
The Software Engineering Manager Role

Eduardo Klein - Nov 4
Mangabo Kolawole
Software Engineer | Technical Writer | Book Author

    Location
    Remote
    Education
    Studied CS
    Work
    Software Engineer
    Joined
    Nov 30, 2019

More from Mangabo Kolawole
How to Quickly Navigate a New Codebase
#productivity #programming #career #beginners
How to Choose the Ideal Database for Your App: Prototypes, App at Scale, and Event-Driven App
#beginners #codenewbie #database #productivity
How to Talk to Non-Developers?
#beginners #career #productivity #programming
profile
Heroku
Promoted

Heroku
This site is built on Heroku

Join the ranks of developers at Salesforce, Airbase, DEV, and more who deploy their mission critical applications on Heroku. Sign up today and launch your first app!

Get Started

# Example Title: Database Choice for User Data

## Context and Problem Statement

We need a scalable database to store and manage user data efficiently as our user base grows.

## Decision Drivers

* Scalability
* Data consistency
* Ease of integration with existing services

## Considered Options

* PostgreSQL
* MongoDB
* Amazon DynamoDB

## Decision Outcome

Chosen option: **PostgreSQL** because it provides strong data consistency and aligns well with our need for complex queries.

### Consequences

* **Good:** Supports ACID compliance, enhancing data reliability.
* **Bad:** May require more tuning to achieve high performance with large datasets.

### Confirmation

We’ll confirm this decision through periodic load tests and performance reviews as the user base scales.

## Pros and Cons of the Options

### PostgreSQL

* **Good:** ACID compliance, robust community support.
* **Neutral:** Setup and tuning can be time-consuming.
* **Bad:** Lacks native horizontal scaling.

### MongoDB

* **Good:** Schema flexibility, horizontal scaling.
* **Bad:** No ACID compliance across collections, limiting data integrity.

## More Information

For additional details, see the database performance evaluation [here](link-to-evaluation).

# Example Title: Database Choice for User Data

## Context and Problem Statement

We need a scalable database to store and manage user data efficiently as our user base grows.

## Decision Drivers

* Scalability
* Data consistency
* Ease of integration with existing services

## Considered Options

* PostgreSQL
* MongoDB
* Amazon DynamoDB

## Decision Outcome

Chosen option: **PostgreSQL** because it provides strong data consistency and aligns well with our need for complex queries.

### Consequences

* **Good:** Supports ACID compliance, enhancing data reliability.
* **Bad:** May require more tuning to achieve high performance with large datasets.

### Confirmation

We’ll confirm this decision through periodic load tests and performance reviews as the user base scales.

## Pros and Cons of the Options

### PostgreSQL

* **Good:** ACID compliance, robust community support.
* **Neutral:** Setup and tuning can be time-consuming.
* **Bad:** Lacks native horizontal scaling.

### MongoDB

* **Good:** Schema flexibility, horizontal scaling.
* **Bad:** No ACID compliance across collections, limiting data integrity.

## More Information

For additional details, see the database performance evaluation [here](link-to-evaluation).

Thank you to our Diamond Sponsor Neon for supporting our community.

DEV Community — A constructive and inclusive social network for software developers. With you every step of your journey.

    Home
    DEV++
    Podcasts
    Videos
    Tags
    DEV Help
    Forem Shop
    Advertise on DEV
    DEV Challenges
    DEV Showcase
    About
    Contact
    Free Postgres Database
    Guides
    Software comparisons

    Code of Conduct
    Privacy Policy
    Terms of use

Built on Forem — the open source software that powers DEV and other inclusive communities.

Made with love and Ruby on Rails. DEV Community © 2016 - 2024.
