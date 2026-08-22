import { defineCollection } from 'astro:content';
import { glob } from 'astro/loaders';
import { z } from 'astro/zod';

const pioneers = defineCollection({
  loader: glob({ pattern: '**/*.md', base: './src/content/pioneers' }),
  schema: z.object({
    name: z.string(),
    title: z.string(),
    birth: z.number(),
    death: z.number(),
    image: z.string(),
    birthDate: z.string().optional(),
    deathDate: z.string().optional(),
    birthPlace: z.string().optional(),
    deathPlace: z.string().optional(),
    excerpt: z.string().optional(),
    railFeature: z.object({
      kicker: z.string().optional(),
      title: z.string(),
      quote: z.string().optional(),
      quoteHighlight: z.string().optional(),
      source: z.string().optional(),
      scriptures: z.array(z.string()).optional(),
      statements: z.array(z.string()).optional(),
      note: z.string().optional()
    }).optional(),
    works: z.array(z.object({
      title: z.string(),
      type: z.enum(['book', 'article', 'sermon', 'tract', 'chart', 'pamphlet', 'letter', 'other']),
      url: z.string(),
      year: z.string().optional(),
      note: z.string().optional()
    })).optional(),
    quotes: z.array(z.object({
      text: z.string(),
      source: z.string().optional(),
      year: z.string().optional()
    })).optional(),
    books: z.array(z.string()).optional(),
    categories: z.array(z.string()).optional()
  })
});

const books = defineCollection({
  loader: glob({ pattern: '**/*.md', base: './src/content/books' }),
  schema: z.object({
    title: z.string(),
    author: z.string(),
    published: z.string(),
    description: z.string(),
    importance: z.string(),
    link: z.string().optional()
  })
});

const apostasy = defineCollection({
  loader: glob({ pattern: '**/*.md', base: './src/content/apostasy' }),
  schema: z.object({
    name: z.string(),
    title: z.string(),
    birth: z.number(),
    death: z.number().optional(),
    birthDate: z.string().optional(),
    deathDate: z.string().optional(),
    birthPlace: z.string().optional(),
    deathPlace: z.string().optional(),
    image: z.string(),
    excerpt: z.string().optional(),
    apostasyDate: z.string().optional(),
    affiliation: z.string().optional(),
    railFeature: z.object({
      kicker: z.string().optional(),
      title: z.string(),
      quote: z.string().optional(),
      quoteHighlight: z.string().optional(),
      source: z.string().optional(),
      scriptures: z.array(z.string()).optional(),
      statements: z.array(z.string()).optional(),
      note: z.string().optional()
    }).optional(),
    works: z.array(z.object({
      title: z.string(),
      type: z.enum(['book', 'article', 'sermon', 'tract', 'chart', 'pamphlet', 'letter', 'other']),
      url: z.string(),
      year: z.string().optional(),
      note: z.string().optional()
    })).optional(),
    quotes: z.array(z.object({
      text: z.string(),
      source: z.string().optional(),
      year: z.string().optional()
    })).optional(),
    categories: z.array(z.string()).optional()
  })
});

export const collections = {
  pioneers,
  books,
  apostasy
};