# outsinc

OUTSINC Pathways: Integrated Outreach and Case Management Ecosystem

The provided text outlines the structural evolution of Saskatchewan’s social infrastructure, specifically detailing the transition from siloed services to an integrated, risk-driven collaborative ecosystem. It highlights the Saskatchewan Hub Model, which utilizes a "Four-Filter Process" to facilitate multi-agency intervention for individuals facing imminent risks like homelessness or overdose while maintaining strict privacy standards. The documents also describe the Provincial Approach to Homelessness (PATH) and various Indigenous-led wellness models, emphasizing a shift toward restorative, decolonized care and decentralized "smaller shelters." Complementing these policy frameworks is a technical proposal for "OUTSINC Pathways," a specialized case management web application designed to streamline outreach through trauma-informed intake forms and real-time data integration. Collectively, these sources present a comprehensive strategy for addressing complex social needs through fiscal investment, standardized assessment tools, and cross-sectoral coordination.

The 60-question smart intake assessment is the core of the LINK platform, designed as a trauma-informed, multi-step wizard to guide vulnerable individuals through a non-judgmental registration process. Every question is fully optional, providing universal escape options such as "Prefer not to answer," "Other," or the ability to skip sections entirely.
The 60-Question Structure: From Beginning to End
The assessment is divided into seven sections to manage user fatigue and organize data logic.
• Section 1: Connection & Consent (Q1–5): Establishes the entry point (e.g., Street Outreach, Drop-in) and builds foundational trust. It captures basic demographics like age and preferred language.
• Section 2: Immediate Crisis & Safety (Q6–15): Focuses on urgent survival needs, including where the user slept, physical pain, food security, and fleeing violence.
• Section 3: Housing History & Goals (Q16–25): Identifies housing barriers, chronicity of homelessness, past evictions, and the presence of legal barriers to renting.
• Section 4: Mental Health & Wellbeing (Q26–35): Screens for diagnosed conditions, current support connections, trauma symptoms (like "spacing out"), and interest in peer support.
• Section 5: Substance Use & Recovery (Q36–43): Explores how use impacts stability and assesses interest in specific local programs like the RAAM Clinic or Red Path Recovery.
• Section 6: Elderly & Retired Resident Specifics (Q44–51): Specifically tailored for seniors (60+), covering pension status, mobility challenges, and medical conditions like heart disease.
• Section 7: Social, Legal & Life Skills (Q52–60): Wraps up with broad supports, including legal court dates, tax assistance, and the user’s primary goal for the next 30 days.

--------------------------------------------------------------------------------
How it Works: The Smart Logic
The assessment is "smart" because it uses dynamic conditional logic to branch or skip questions based on real-time responses, ensuring users only answer what is relevant to them.
• Progressive Disclosure and Branching:
    ◦ Age-Gating: If a user selects "60+ (Senior)" in Section 1, the system unlocks the elderly-specific questions in Section 6. If they are "Under 18," it triggers immediate youth protection alerts for staff.
    ◦ Need-Based Skips: If a user indicates substance use does not impact their housing (Q36), the system skips the remainder of Section 5 to reduce unnecessary probing.
    ◦ Escalation: Answering "Yes" to fleeing violence (Q10) immediately reveals sub-questions about the need for safe shelter.
• Risk Scoring and Prioritization:
    ◦ The system uses rule-based AI to analyze "Crisis Clusters". For example, a user who is sleeping outdoors, in severe pain, and fleeing violence is automatically flagged as "High Priority" for staff review.
• Consent Management:
    ◦ The logic respects the user's "Consent to Coordinate" (Q4). If consent is "No" or "Partial," the system disables auto-referrals and restricts data sharing to protect privacy.

--------------------------------------------------------------------------------
What it Does: Automated Outcomes
Upon completion, the assessment engine validates the data and triggers several automated actions across the ecosystem:
• Auto-Generated Tasks: Specific tasks are assigned to the client’s FOOTPRINT dashboard and the staff's DCIDE dashboard. For example, answering "No" to having a photo ID (Q23) triggers a task for an "ID Clinic Appointment".
• Personalized Goals: Long-term objectives are established based on responses. A report of chronic homelessness (1 year+) automatically creates a "Permanent Supportive Housing Pathway" goal.
• Referrals and Notifications: Real-time alerts (via SMS, email, or Socket.io) are sent to staff or providers. If a user requests a referral to the RAAM Clinic (Q37), the system can auto-send a referral with the user's consented data.
• Achievements and Gamification: To empower the user, completing the assessment unlocks the "Intake Completer Badge," while specific sections might unlock achievements like the "Courage Badge" for seeking safety or the "Documentation Starter" for starting the ID process.
• System Integration: The assessment syncs with a mock HIFIS API, automatically adding eligible, consenting individuals to local By-Name Lists for housing prioritization.
provide me all actual 60 questions and the choices to pick for responses

The following is the complete list of all 60 questions and the specific response options for the LINK platform's smart intake assessment. Every question is designed to be fully optional, providing universal escape options including "Prefer not to answer," "Other (please describe)," and the ability to skip.
Section 1: Connection & Consent (Q1–5)
1. How did you connect with us today? (Street outreach / 310 Division Street drop-in / Online form / website / Referral from another agency or person).
2. What age range do you fall into? (Under 18 / 18–24 / 25–59 / 60 or older (senior)).
3. What language do you feel most comfortable using right now? (English / French / American Sign Language (ASL)).
4. Do you give us permission for a case worker to help coordinate services for you (housing, health care, employment, etc.) with other organizations? (Yes – full permission / Partial permission (I want to choose what gets shared) / No – no coordination please).
5. Would you like us to use a nickname or different name when we talk about you (for privacy/safety reasons)? (No, use my real name / Yes – my preferred name is: ________).
Section 2: Immediate Crisis & Safety (Q6–15)
6. Where did you sleep last night? (Outdoors (street, park, encampment, car, etc.) / Emergency shelter / Cobourg Warming Room / Sofa surfing / couch surfing / With friend or family).
7. Are you in physical pain or do you need to see a doctor right away? (Yes – severe pain/emergency / Yes – mild to moderate pain / No).
8. When was your last full meal? (Today / Yesterday / 2 or more days ago).
9. Do you feel safe where you are sleeping right now? (Yes / No / Sometimes/it depends).
10. Are you currently trying to get away from violence or abuse? (Yes – I need immediate safe shelter (Cornerstone or similar) / No – not right now / This happened in the past).
11. Do you have a safe place to keep your belongings? (Yes / No / Only what I can carry).
12. Do you have reliable access to clean drinking water right now? (Yes / No / Sometimes/not consistent).
13. Do you have clothing that is appropriate for the current weather? (Yes / No / Needs repair/incomplete).
14. Do you have a pet that needs to stay with you? (Yes / No).
15. Are you part of a couple and need to stay together? (Yes / No).
Section 3: Housing History & Goals (Q16–25)
16. How long has it been since you had your own permanent housing? (Never had permanent housing / Less than 6 months / 6–12 months / More than 1 year).
17. How many separate times have you experienced homelessness in the last 3 years? (1 time / 2–3 times / 4 or more times).
18. Have you ever been evicted from a place in Cobourg or Northumberland County? (Yes / No).
19. Are there any legal issues making it harder to get housing right now? (Bail conditions / Owing rent/arrears / Criminal record / None of these).
20. What do you think is the biggest barrier to getting housing right now? (Cost/rent too high / No photo ID or documents / Past evictions / Credit history / Health issues).
21. Have you stayed at Transition House (310 Division) or 10 Chapel Street before? (Yes / No).
22. Are you currently on the Northumberland County housing waitlist? (Yes / No / I don’t know).
23. Do you have a valid government-issued photo ID right now? (Yes / No / It’s expired).
24. Do you have a Social Insurance Number (SIN)? (Yes / No).
25. If housing becomes available, what kind would you prefer? (Private apartment (scattered site) / Group/congregate setting / No strong preference).
Section 4: Mental Health & Wellbeing (Q26–35)
26. Have you ever been told by a doctor that you have a mental health condition (depression, anxiety, PTSD, bipolar, schizophrenia, etc.)? (Yes / No / I am currently seeking an assessment).
27. Are you already connected with the NHH Community Mental Health Team or another mental health service? (Yes / No).
28. Would you like us to help arrange a referral to the Walk-In Counselling Clinic on Elgin Street? (Yes / No).
29. Do you ever experience times when you "space out," have memory gaps, or feel disconnected after difficult experiences? (Frequently / Sometimes / Never).
30. Have you ever had a serious head injury (concussion, fall, accident, etc.)? (Yes / No).
31. Are you currently taking any prescribed medication for mental health? (Yes / No / I had some but lost them/ran out).
32. Do you have a written crisis prevention or self-care plan? (Yes / No / I would like help making one).
33. In high-stress situations, do you sometimes feel a loss of control over your thoughts, feelings, or actions? (Yes / No / Sometimes).
34. Would you like to speak with a Peer Support Worker? (Yes / No).
35. Do you find yourself struggling with hoarding items, collecting things, or finding it hard to let things go? (Yes / No).
Section 5: Substance Use & Recovery (Q36–43)
36. Do you feel that your use of substances is making it harder to keep housing or stay stable? (Yes / No).
37. Would you like a referral to the RAAM (Rapid Access Addiction Medicine) Clinic? (Yes / No).
38. Are you interested in the Red Path Recovery Program or similar local recovery supports? (Yes / No).
39. Do you currently have access to Naloxone (Narcan) or other overdose prevention tools? (Yes / No).
40. How many days or months have you been in recovery (if applicable)? (0–90 days / 91–180 days / More than 1 year / Not currently in recovery/NA).
41. Is most of your current social circle made up of people who use substances? (Yes / No / Mixed).
42. Do you sometimes use substances to help manage emotional pain or difficult memories? (Yes / No).
43. Which kind of support feels right for you right now? (Harm reduction / Abstinence-only programs / Both approaches are okay).
Section 6: Elderly & Retired Resident Specifics (Q44–51)
44. Are you currently receiving Old Age Security (OAS), Canada Pension Plan (CPP), or other pension income? (Yes / No / Application is pending).
45. Did you lose your housing because a retirement residence or long-term care home closed or changed its rules? (Yes / No).
46. Do you have any mobility challenges that would require an accessible unit? (Yes – wheelchair required / Yes – walker or cane / No).
47. Would you like a regular "Senior Check-in" service for safety and wellness? (Yes / No).
48. Is there a family member, friend, or Power of Attorney we should contact to help support you? (No / Yes – name and contact: ________).
49. Do you live with significant ongoing medical conditions (diabetes, heart disease, COPD, etc.)? (Yes / No).
50. Are you 60 years of age or older? (Yes / No).
51. Do you need help with meal planning, grocery shopping, or preparing food? (Yes / No).
Section 7: Social, Legal & Life Skills (Q52–60)
52. Do you have a regular family doctor (GP) in Northumberland County? (Yes / No).
53. Do you have any upcoming court dates or legal proceedings? (Yes / No).
54. Would you be interested in life skills programming (budgeting, cooking, computer basics, etc.)? (Yes / No).
55. Do you feel confident in your ability to advocate for yourself or navigate systems ("street smart")? (Yes / No / Sometimes).
56. Is anyone currently forcing, pressuring, or tricking you into doing things you do not want to do? (Yes / No).
57. If we could help arrange it, do you have a friend or family member you could stay with temporarily? (Yes / No).
58. Do you currently have any meaningful daily activity (paid work, volunteering, hobbies, routine)? (Yes / No).
59. Would you like help filing your income tax returns to access credits or benefits? (Yes / No).
60. What is the #1 goal or change you would most like to see happen in the next 30 days? ([Free text box]).







--------------- WORKFLOW FOR CLIENJTS


The client workflow in the OUTSINC Pathways ecosystem is a trauma-informed, "low-barrier" journey designed to move an individual from a state of crisis to long-term stability. The process is divided into several distinct phases, primarily utilizing the LINK platform for entry and the FOOTPRINT platform for ongoing progress.
Phase 1: Discovery and the "Zero-Judgment" Entry
A client’s first interaction begins at the Landing Page, which features a "Zero-Judgment Pledge Splash" to build immediate trust. They are presented with four clear paths:
• Get Connected Now: Quick action buttons for those in immediate crisis (24/7 support) or those needing a public resource hub without logging in.
• Tell Your Story: This leads to the Self-Registration Wizard on the LINK platform, which follows the "Tell Your Story Once" principle.
Phase 2: Self-Registration and Privacy Setup
During registration, the system captures minimal fields to establish an account while prioritizing safety:
• Identity: Clients can choose to use an alias or "street name" for privacy. fields : first name, last name, 
• Demographics: Basic info like dae of birth and preferred language (English, French, or ASL) is collected.
• Consent: Clients use granular sliders or checkboxes to decide which providers can see their data. If they select "No" to coordination (Q4), the system automatically skips all future referral generation to respect their choice.
• Account setup -   We ask the user to  create a username , and then create a password, and then  confirm password, and also answer a security question (  a selection from 1-10 optiopns from a drop down), and then  an input field to answer security question. 

Phase 3: The Smart Assessment (Tell Your Story Once)
Once registered, the client enters the 60-question smart intake assessment. This is a dynamic, multi-step wizard where every question is fully optional.
• Dynamic Branching: The logic skips irrelevant questions to reduce "trauma fatigue". For example, if a user indicates substance use does not impact their housing (Q36), the system skips the entire remainder of the substance use section.
• Automated Triggers: As the client answers, the backend rule-based AI creates real-time outcomes:
    ◦ Tasks: If they report lacking a photo ID (Q23), an "ID Clinic Appointment" task is automatically added to their dashboard.
    ◦ Goals: Indicating chronic homelessness (1 year+) triggers a "Permanent Supportive Housing (PSH) Pathway" goal.
    ◦ Alerts: Answering "Yes" to fleeing violence (Q10) immediately triggers a notification to staff and partners like Cornerstone for urgent safety planning.
Phase 4: The Journey Dashboard (FOOTPRINT)
Upon completing the intake, the client is redirected to their FOOTPRINT dashboard, their personal empowerment hub.
• Journey Timeline: A visual scrollable timeline shows their path from intake to housing. Completing the assessment triggers a "Milestone Celebration" with an "Intake Completer Badge" and on-screen confetti.
• Goal Tracking: Clients use progress sliders to track their goals (e.g., "Achieve 90 Days Sober" or "Stable Housing in 6 Months").
• Daily Tasks: A simple to-do list allows clients to check off items like "Attend GP Appointment" or "Upload ID Photo".
Phase 5: Ongoing Case Management and Coordination
As the client moves toward stability, they utilize several secure tools:
• Document Vault: They can scan and store IDs, medical letters, or meds, and explicitly choose when to share these with their caseworker.
• Secure Messaging: An encrypted chat allows real-time communication with their outreach team or service providers.
• Referrals: When a staff member creates a referral (e.g., to a RAAM Clinic), the client reviews and approves it on their dashboard. They can see the real-time status of their referrals and waitlists.
Phase 6: Long-Term Stability and Transition
The workflow concludes as the client reaches their major milestones:
• Graduation: As goals are completed, more badges are unlocked (e.g., "Housing Hero Milestone").
• By-Name List Integration: If the client has consented, their profile is synced with the local HIFIS By-Name List for prioritized housing placement.
• Follow-Up: Even after securing housing, the system uses a Follow-up Module to send auto-reminders for 6-month and 12-month housing stability checks to ensure the client remains stable and supported






  ACCOUNT ISSUES ?  if a user has forgot their account username, or password,  thgey can go through the password reset process, in which  the client will provide first name, last name  and date of birth,  when that is given the database will be checked and if found,  it will display the security question, in which the client must answer the  security questio answer, if answered correctly please  then provide the clients username to them, and allow them to reset their password, int othe two password fields to create a new password.
