# expertai
Expert.ai Stock Sentiment Analysis Repository

This repository houses the code for the Expert.ai Stock Sentiment Analysis project, built for their hackathon in 2021.
The purpose of the project was to explore if the sentiment analysis of various SeekingAlpha news items pertaining to a particular company affecred the stock performance of said company. I wanted to explore if there was some correlation, whether positive or negative, between the sentiment given to the articule by expertai and the stock price for the next day, next third day and next fifth day.

The project is old-school PHP with MySQL and JQuery.  I used Chart.js in order to graph the stock price trend.  The news articles were scraped from SeekingAlpha, and the stories are from May 1st until May 28th.  I rounded up over 5,000 articles, and each one was run through the expertai API to obtain their sentiment score.  This score was then added to the article.

The stock price data was obtained from stooq.com and ranges from May 3rd until June 7th, giving us a data set containing the first news article's date and lasting until the fifth trading day (keeping in mind that May 31st was a holiday and the market was closed) after May 28.

The stock prices are tracked as follows:
1) News article dated on a weekend (anytime Saturday or Sunday): stock price is taken at open the next valid market date.
2) News article dated on a weekday: stock price is taken at the close of the date of ocurrence.

The stock prices are compared with the respective open or close price after 1 day, 3 days and 5 days.

The demo is live here: http://stocksentimentai.com

The results were astounding...

Negative sentiment articles resulted in the stock price dropping, on the average, 80% of the time, after 1 day.  The average drop in stock price after one day was 1.68%.  This trend was not as sharp after the third day and the fifth day, however.  On the third day, the stock had dropped 64% of the time, and by the fifth day, this was down to 52% of the time, relative to the initial price.  We can conclude that "bad news" tends to drive the price down in the short-term, and it then begins to correct itself longer-term.

Positive sentiment articles were more impressive - there resulted in the stock price rising, on the average, 85% of the time after 1 day, 79% of the time after 3 days and 74% of the time after five days.  Interestingly enough, the stock price rose by 1.17% after 1 day, 1.35% after the third day and 1.29% after the fifth day, relative to the initial price.  We can conclude that "good news" tends to drive the price up and it takes longer for the price to "spike" (in this case, the third day) before coming back down again.

Next steps in this project would include:
1) Incorporating multiple sources of stock news (Wall Street Journal, Benzinga, CNN, MarketWatch, etc).
2) Daily updates to the stock market open/close price data.
3) Stock price predictions based on Monte Carlo simulations and incorporating the sentiment analysis as a variable.
