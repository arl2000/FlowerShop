<?php
/**
 * Bouquet Wrapper Component
 * 
 * This file provides a reusable bouquet wrapper component that shows only the
 * wrapper without flowers or ribbon, as requested.
 * 
 * Usage: include 'bouquet_wrapper_component.php';
 */
?>

<div class="bouquet-wrapper-component">
    <style>
        .bouquet-wrapper-component {
            display: inline-block;
            margin: 20px;
        }
        
        .wrapper-container {
            position: relative;
            width: 300px;
            height: 400px;
            filter: drop-shadow(0 10px 15px rgba(0,0,0,0.1));
            transition: transform 0.3s ease;
        }
        
        .wrapper-container:hover {
            transform: rotate(1deg) scale(1.02);
        }
        
        .wrapper {
            position: absolute;
            width: 300px;
            height: 300px;
            background-color: #ffffff;
            border-radius: 50% 50% 0 0;
            transform-origin: bottom center;
            z-index: 1;
            box-shadow: inset 0 0 30px rgba(0,0,0,0.05);
            overflow: hidden;
            animation: gentle-sway 8s infinite ease-in-out;
        }
        
        @keyframes gentle-sway {
            0% { transform: rotate(0deg); }
            25% { transform: rotate(0.5deg); }
            50% { transform: rotate(0deg); }
            75% { transform: rotate(-0.5deg); }
            100% { transform: rotate(0deg); }
        }
        
        .wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 30% 20%, rgba(255,255,255,0.8) 0%, rgba(255,255,255,0) 50%),
                radial-gradient(circle at 70% 80%, rgba(255,255,255,0.8) 0%, rgba(255,255,255,0) 60%);
        }
        
        .wrapper-fold {
            position: absolute;
            background-color: #fafafa;
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .fold-left {
            width: 150px;
            height: 300px;
            top: 0;
            left: 0;
            transform-origin: right center;
            transform: rotate(-15deg);
            border-radius: 50% 0 0 0;
            z-index: 3;
            box-shadow: 
                inset 0 20px 30px -20px rgba(0,0,0,0.1),
                5px 0 15px -8px rgba(0,0,0,0.06);
            animation: fold-left-sway 10s infinite ease-in-out;
        }
        
        @keyframes fold-left-sway {
            0% { transform: rotate(-15deg); }
            30% { transform: rotate(-14deg); }
            60% { transform: rotate(-15.5deg); }
            100% { transform: rotate(-15deg); }
        }
        
        .fold-right {
            width: 150px;
            height: 300px;
            top: 0;
            right: 0;
            transform-origin: left center;
            transform: rotate(15deg);
            border-radius: 0 50% 0 0;
            z-index: 3;
            box-shadow: 
                inset 0 20px 30px -20px rgba(0,0,0,0.1),
                -5px 0 15px -8px rgba(0,0,0,0.06);
            animation: fold-right-sway 9s infinite ease-in-out;
        }
        
        @keyframes fold-right-sway {
            0% { transform: rotate(15deg); }
            30% { transform: rotate(15.5deg); }
            60% { transform: rotate(14.5deg); }
            100% { transform: rotate(15deg); }
        }
        
        .fold-bottom {
            width: 300px;
            height: 150px;
            bottom: 50px;
            transform: rotate(180deg);
            border-radius: 50% 50% 0 0;
            z-index: 2;
            box-shadow: inset 0 -20px 30px -20px rgba(0,0,0,0.1);
        }
        
        .stem-wrapper {
            position: absolute;
            width: 60px;
            height: 100px;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            background-color: #fafafa;
            border-left: 1px solid rgba(0,0,0,0.05);
            border-right: 1px solid rgba(0,0,0,0.05);
            z-index: 1;
        }
        
        /* Paper texture and wrinkles */
        .paper-texture {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23000000' fill-opacity='0.02' fill-rule='evenodd'/%3E%3C/svg%3E");
            opacity: 0.5;
            z-index: 5;
            pointer-events: none;
        }
        
        /* Wrinkles in the paper */
        .wrinkle {
            position: absolute;
            background: linear-gradient(90deg, rgba(0,0,0,0) 0%, rgba(255,255,255,0.7) 50%, rgba(0,0,0,0) 100%);
            transform: rotate(var(--rotation, 0deg));
            opacity: 0.4;
            z-index: 4;
        }
        
        .wrinkle-1 {
            width: 120px;
            height: 2px;
            top: 20%;
            left: 30%;
            --rotation: 30deg;
        }
        
        .wrinkle-2 {
            width: 150px;
            height: 2px;
            top: 60%;
            left: 10%;
            --rotation: -20deg;
        }
        
        .wrinkle-3 {
            width: 100px;
            height: 2px;
            top: 40%;
            left: 50%;
            --rotation: 60deg;
        }
        
        .wrinkle-4 {
            width: 120px;
            height: 2px;
            top: 70%;
            left: 60%;
            --rotation: -45deg;
        }
        
        /* Fold creases - the details that make it look realistic */
        .fold-crease {
            position: absolute;
            background: linear-gradient(90deg, rgba(255,255,255,0.3) 0%, rgba(0,0,0,0.05) 50%, rgba(255,255,255,0.3) 100%);
            height: 1px;
            transform: rotate(var(--crease-angle, 0deg));
            z-index: 4;
        }
        
        .fold-crease-1 {
            width: 100px;
            top: 25%;
            left: 25%;
            --crease-angle: 15deg;
        }
        
        .fold-crease-2 {
            width: 80px;
            top: 45%;
            right: 25%;
            --crease-angle: -25deg;
        }
        
        .fold-crease-3 {
            width: 120px;
            bottom: 30%;
            left: 20%;
            --crease-angle: 0deg;
        }
        
        /* Subtle paper edge variations */
        .paper-edge {
            position: absolute;
            height: 2px;
            background-color: rgba(255,255,255,0.7);
            border-radius: 2px;
            box-shadow: 0 0 5px rgba(0,0,0,0.03);
            z-index: 5;
        }
        
        .edge-top-left {
            width: 80px;
            top: 10px;
            left: 10px;
            transform: rotate(25deg);
        }
        
        .edge-top-right {
            width: 60px;
            top: 15px;
            right: 20px;
            transform: rotate(-15deg);
        }
    </style>
    
    <div class="wrapper-container">
        <div class="wrapper"></div>
        <div class="wrapper-fold fold-left"></div>
        <div class="wrapper-fold fold-right"></div>
        <div class="wrapper-fold fold-bottom"></div>
        <div class="stem-wrapper"></div>
        
        <!-- Paper texture overlay -->
        <div class="paper-texture"></div>
        
        <!-- Wrinkles in the paper -->
        <div class="wrinkle wrinkle-1"></div>
        <div class="wrinkle wrinkle-2"></div>
        <div class="wrinkle wrinkle-3"></div>
        <div class="wrinkle wrinkle-4"></div>
        
        <!-- Fold creases -->
        <div class="fold-crease fold-crease-1"></div>
        <div class="fold-crease fold-crease-2"></div>
        <div class="fold-crease fold-crease-3"></div>
        
        <!-- Paper edge highlights -->
        <div class="paper-edge edge-top-left"></div>
        <div class="paper-edge edge-top-right"></div>
    </div>
</div> 