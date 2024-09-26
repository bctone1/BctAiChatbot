=== BCT AI Chatbot ===
Contributors: BCTONE
Tags: chatgpt/embedding, tts/stt, ai chatbot, chatbot template, statistics
Requires at least: 6.4
Tested up to: 6.5.2
Stable tag: 0.9.5
Requires PHP: 7.0 or higher
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

ChatGPT, Embeddings, AI Training, STT/TTS, Custom Post Types


== Integrations ==
* OpenAI: GPT models, Whisper
* Pinecone for building content and long term external memory for chat bot


== Features ==
* Generate high-quality, longer articles using OpenAI's GPT-3 language model (text-davinci-003) and GPT-3.5 Turbo.
* Customize the generated content with options for temperature, maximum tokens, top p, best of, frequency penalty, and presence penalty.
* Customize the generated content with options for writing style, including informative, descriptive, creative, narrative, persuasive, expository, reflective, argumentative, analytical, critical, evaluative, journalistic, technical and simple.
* Adjust the tone of the generated content with options for formal, neutral, assertive, cheerful, humorous, informal, inspirational, sarcastic, professional and skeptical.
* Easily manage and update your OpenAI API key from within the plugin's settings page.
* Track logs
* Embed GPT chatGPT in front-end.
* Chatbot tone and proffesion options.

* Embeddings! Customize your chat bot with embeddings - Integrated with Pinecone vector DB.
* Index builder for Embeddings! Convert all your pages, posts and products to embedding format with one click.
* GPT powered semantic search with Embeddings

* Train your AI. Fine-Tune and create your own model.
* Convert your db to jsonl format for AI training.



== Supported Languages ==
* English
* Korean

== Service URL ==
# https://texttospeech.googleapis.com/v1/ : The Google Text-to-Speech API is accessed using the following URL

# https://[Pinecone Environment]/vectors/delete: URL for deleting an index from the Pinecone vector storage

# https://'. $bctai_pinecone_environment . '/vectors/delete?ids='.$id: URL for deleting an index from the Pinecone vector storage

# https://'. $bctai_pinecone_environment . '/vectors/upsert: URL for saving to the Pinecone vector storage

# https://controller.' + bctai_pinecone_sv + '.pinecone.io/databases: URL for requesting Pinecone index












== Changelog ==

Version 0.9.5– 09.26 2024
====================================================
Fix: Resolve CSS conflicts in the Hello Elementor theme

Version 0.9.4– 09.20 2024
====================================================
Update: Added responsive CSS for mobile (src/mobile.css)

Version 0.9.3– 09.19 2024
====================================================
Update: Updated and modified external attachment scripts

Version 0.9.2– 09.12 2024
====================================================
Update: Added STT usage
Update: Created STT DB table
Fix: Fixed TTS error
Fix: Resolved embedding post deletion error

Version 0.9.1– 09.09 2024
====================================================
Fix: Resolved TTS usage error in Statistics / Dashboard

Version 0.8.6.2– 09.09 2024
====================================================
Fix: Temporarily removed "remember conversation"
Update: Added image attachment feature

Version 0.8.6.1– 09.09 2024
====================================================
Update: Added Menu module

Version 0.8.6– 09.04 2024
====================================================
Fix: Resolved TTS error (major class name changes)
Fix: Deleted shortcode code
Update: Changed message when there are no embedding results

Version 0.8.5.7– 09.01 2024
====================================================
Update: Added LLM provider and models (Google Gemini)
Update: Added embedding models (Google text-embedding-004, embedding-004)


Version 0.8.5.6– August.01 2024
====================================================
Update: Added OpenAI LLMs: 'gpt-4-0613', 'gpt-4-0314', 'gpt-4o', 'gpt-4o-mini'
Update: Modified admin and front-end UI; added properties for Qdrant and OpenRouter in bctai-chat-settings

Version 0.8.5.4– August.01 2024
====================================================
Fix: Major style revisions and <a> tag error resolution

Version 0.8.5.3– July.22 2024
====================================================
Fix: Qdrant score adjustment and endpoint check
Fix: Embedding model selection error
Fix: Chatbot <a> tag UI adjustment


Version 0.8.5.2– July.09 2024
====================================================
Fix: STT error correction
Update: Added pagination to the Q&A List

Version 0.8.5– July.01 2024
====================================================
Update: Modularized certain features (on/off format)
Update: Added STT WER Test page

Version 0.8.2– June.04 2024
====================================================
Fix: Compressed CSS and JS files and added src folder
Fix: Remote file calls and URL information update

Version 0.8.1– May.27 2024
====================================================
Fix: Fixed null return value in "Datasets / Create Fine-Tune"
Fix: Fixed null return value in "Trainings / Event button"
Update: Added embedding post link input and updated cosine similarity information


Version 0.8.0– May.13 2024
====================================================
Update: Added "Chatbot / Settings Fine-tuning" button
Fix: Chatbot / Displaying cosine similarity up to 2 decimal places



Version 0.7.9– May.09 2024
====================================================
Fix: Embedding + LLM function error
Fix: Fine-tuning / data convert, upload, Data Entry function error


Version 0.7.8– May.08 2024
====================================================
Update: Added kboard post embedding function



Version 0.7.7– April.30 2024
====================================================
Fix: admin / views / chart / dashboard.php / date select error & query 


Version 0.7.6– April.30 2024
====================================================
Fix: classes / bctai_chat.php / ajax error line.542 ~ 619





Version 0.7.5– April.30 2024
====================================================
Update: admin / extra / bctai_chat_widget_design.php -> custom design(for Pro) activate
Update: admin / extra / bctai_chat_widget_design.php -> Added chatbot name change function
Update: admin / views / chart / dashboard.php -> graf style

Version 0.7.4– April 2024
====================================================
Add management/statistics design
Embedding content deletion error correction








= 0.7.3.2 =
Delete submenu duplicate error
Statistics / Dashboard / audio_logs->delete source (error correction)

= 0.7.2 =
finecone api setting sv deletion and code cleanup
pdf pro page limits
Add visitor and page view statistics
Statistics / Dashboard / Add Userview, pageview, newmember
Fix PDF embedding error

= 0.7.1 =
Modify overall style
Separate design and settings
Submenu error correction
freemius Add
Statistics / Dashboard  delete graf shortcode and prompt 

= 0.7 =
Add Korean translation file

= 0.6.9 =
Add PDF embedding
Add chatbot design template
Change embedding code (delete post content)
Add chatbot full screen
Add Contact Us

= 0.6.8 =
Delete prompt
Add System message
Fix custom icon error

= 0.6.7 =
add embedding contents acf field
STT, STA selection function added
Fix web speech api error

= 0.6.6 =
Add chatbot chat records

= 0.6.5 =
Add fineone score

= 0.6.4 =
Add Dashboard page prompt usage chart
Modify prompt default settings

= 0.6.3 =
Fixed submenu at the top of the plugin
Add audio usage (TTS) chart to Dashboard page

= 0.6.2 =
Embedding / Content builder / Add height setting according to the number of textarea characters
Edit submenu slug

= 0.6.1 =
Add token value by date to Dashboard page embedding

= 0.6.0 =
Add chart submenu
Add token value by date to Dashboard page chatbot (widget)

= 0.5.0 =
Audio 

= 0.4.4 =
Enable shortcode

= 0.4.2 =
Add Custom Post Type

= 0.4.1 =
Custom Post Type: Add Instant Embedding button 
Custom Post Type: Add Standard, meta key

= 0.4.0 =
Chatbox Advancement
    add tts (via google api)

Embedding Advancement => 
    add Custom Post Type
    change Pinecone environment code
AI Training Advancement =>


= 0.3.0 =
Chatbox resettings

= 0.2.0 =
Embedding
AI Training


= 0.03 =
Embeddings

= 0.02 =
ChatGPT submenu
    Shortcode Tab
    Widget Tab
    Chat Bots Tab
    Logs Tab

= 0.01 =
Settings > Welcome Tab
Settings > AI Engine Tab



