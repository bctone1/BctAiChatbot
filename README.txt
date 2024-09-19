=== BCT AI Chatbot ===
Contributors: BCTONE
Tags: chatgpt/embedding, tts/stt, ai chatbot, chatbot template, statistics
Requires at least: 6.4
Tested up to: 6.5.2
Stable tag: 0.9.3
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

# https://[Pinecone Environment]/vectors/delete : 파인콘 벡터 저장소 인덱스 삭제요청 url

# https://' . $bctai_pinecone_environment . '/vectors/delete?ids='.$id : 파인콘 벡터 저장소 인덱스 삭제요청 url

# https://' . $bctai_pinecone_environment . '/vectors/upsert : 파인콘 벡터 저장소 저장 요청 url

# https://controller.' + bctai_pinecone_sv + '.pinecone.io/databases : 파인콘 인덱스 요청 url












== Changelog ==

Version 0.9.3– 09.19 2024
====================================================
부부펫(외부부착 스크립트 최신화 및 수정)

Version 0.9.2– 09.12 2024
====================================================
STT사용량 추가, STT DB Table생성, TTS오류 수정
Embedding Post삭제 오류 수정

Version 0.9.1– 09.09 2024
====================================================
Statistics / Dashboard / TTS Usage 오류수정


Version 0.8.6.2– 09.09 2024
====================================================
remember conversation 임시삭제
첨부파일 이미지 추가

Version 0.8.6.1– 09.09 2024
====================================================
Menu 모듈 추가

Version 0.8.6– 09.04 2024
====================================================
TTS 오류 해결 (class명 대폭 수정)
Shorcode 코드 삭제
Embedding결과 없을 시 멘트 변경

Version 0.8.5.7– 09.01 2024
====================================================
LLM공급사 및 모델 추가 (Google Gemini)
임베딩 모델 추가 (Google text-embedding-004, embedding-004)


Version 0.8.5.6– August.01 2024
====================================================
OpenAI LLM 추가 'gpt-4-0613','gpt-4-0314','gpt-4o','gpt-4o-mini'
관리자 및 프론트 디자인 변경, bctai-chat-settings qdrant, Openrouter 속성 추가


Version 0.8.5.4– August.01 2024
====================================================
스타일 대폭수정, a링크 오류 해결



Version 0.8.5.3– July.22 2024
====================================================
Qdrant 스코어 너무 낮아서 엔드포트 맞게 사용했는지 확인 필요! = 임베딩모델 선택 오류 해결

이박사님 요청사항 : 임베딩시 a링크 삭제 말고 챗봇창에 그대로 링크 같이 출력하는 코드 작성 필요 작업중★★★


Version 0.8.5.2– July.09 2024
====================================================
STT 오류 수정
Q&A List pagenation 추가


Version 0.8.5– July.01 2024
====================================================
Update : 일부기능 모듈화(온오프형식)
Update : STT WER Test페이지 추가
Fix : 

Version 0.8.2– June.04 2024
====================================================
Fix : css,js파일 압축 및 src폴더 추가
Fix : 원격파일 호출 및 url정보 작성

Version 0.8.1– May.27 2024
====================================================
Fix : Datesets / Create Fine-Tune 리턴값 null 수정
Fix : Trainings / 이벤트 버튼튼 리턴값 null 수정
Update : Embedding Post 링크 추가입력 및 코사인 유사도 정보 변경


Version 0.8.0– May.13 2024
====================================================
Fix : Chatbot / Settings Fine-tuning 버튼 추가
Fix : Chatbot / 코사인 유사도 소수점 2자리까지만 출력



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



