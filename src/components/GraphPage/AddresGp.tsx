import React, { useState, useEffect, useMemo } from "react";
import "../../styles/GraphPage/DeviceGP.css";
import MapGP from "./MapGP";
import { DateFromServInterface } from "../../LogicComp/GPFakeData";
import SortButtonAdd from "../buttons/SortButtonAdd";
import { Pie } from "react-chartjs-2";
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  ChartOptions,
  ChartData,
} from "chart.js";
import ChartDataLabels from "chartjs-plugin-datalabels";
import { usePremium } from "../../LogicComp/DataProvider";


ChartJS.register(Title, Tooltip, Legend, ArcElement, ChartDataLabels);

interface AddresGpInt {
  Dates: DateFromServInterface[];
}

interface DualData {
  country: string;
  clicks: number;
  country_code: string;
}

const AddresGp = ({ Dates }: AddresGpInt) => {
  const [flag, setFlag] = useState(false); 
  const [data, setData] = useState<DualData[]>([]);
  const [Countries, setCountries] = useState<DualData[]>([]);
  const [City, setCity] = useState<DualData[]>([]);
  const [currentIndex, setCurrentIndex] = useState(0);
  const [sortOption, setSortOption] = useState(0);


  const { isPremium } = usePremium();
  const [isPro] = useState(
    isPremium !== "free" && isPremium !== "base"
  );
  const categories = useMemo(() => [
    { name: "Страны", data: Countries },
    { name: "Города", data: City },
  ], [Countries, City]);

  useEffect(() => {
    const countries: DualData[] = [];
    const city: DualData[] = [];

    Dates.forEach((value) => {
      let countryFlag = false;
      countries.forEach((country) => {
        if (country.country === value.country) {
          country.clicks++;
          countryFlag = true;
        }
      });
      if (!countryFlag) {
        countries.push({
          country: value.country,
          clicks: 1,
          country_code: value.country_code,
        });
      }

      let cityFlag = false;
      city.forEach((cityData) => {
        if (cityData.country === value.city) {
          cityData.clicks++;
          cityFlag = true;
        }
      });
      if (!cityFlag) {
        city.push({
          country: value.city,
          clicks: 1,
          country_code: value.country_code,
        });
      }
    });

    setCountries(countries);
    setCity(city);
    setData(countries);
  }, [Dates]);

  useEffect(() => {
    setData(categories[currentIndex].data);
  }, [currentIndex, categories]);

  useEffect(() => {
    let sortedData = [...categories[currentIndex].data];
    switch (sortOption) {
      case 0:
        sortedData = [...categories[currentIndex].data];
        break;
      case 1:
        sortedData.sort((a, b) => a.country.localeCompare(b.country));
        break;
      case 2:
        sortedData.sort((a, b) => b.country.localeCompare(a.country));
        break;
      case 3:
        sortedData.sort((a, b) => b.clicks - a.clicks);
        break;
      case 4:
        sortedData.sort((a, b) => a.clicks - b.clicks);
        break;
      default:
        break;
    }
    setData(sortedData);
  }, [sortOption, categories, currentIndex]);

  // const handlePrev = () => {
  //   setCurrentIndex((prevIndex) =>
  //     prevIndex === 0 ? categories.length - 1 : prevIndex - 1
  //   );
  // };

  // const handleNext = () => {
  //   setCurrentIndex((prevIndex) =>
  //     prevIndex === categories.length - 1 ? 0 : prevIndex + 1
  //   );
  // };

  const columns = [
    { label: "Алфавит ↓", value: 1 },
    { label: "Алфавит ↑", value: 2 },
    { label: "По кликам ↓", value: 3 },
    { label: "По кликам ↑", value: 4 },
  ];

  
  const processPieData = (data: DualData[]) => {
    const totalClicks = data.reduce((sum, item) => sum + item.clicks, 0);
    const minDegree = 15;
    const minClicks = (minDegree / 360) * totalClicks;

    const maxOtherClicks = (30 / 360) * totalClicks;

    const sortedData = [...data].sort((a, b) => b.clicks - a.clicks);

    const topData = sortedData.filter((item) => item.clicks >= minClicks);
    const otherData = sortedData.filter((item) => item.clicks < minClicks);

    if (otherData.length > 0) {
      const otherClicks = otherData.reduce((sum, item) => sum + item.clicks, 0);
      const limitedOtherClicks = Math.min(otherClicks, maxOtherClicks);

      topData.push({
        country: "Другое",
        clicks: limitedOtherClicks,
        country_code: "",
      });

      if (otherClicks > maxOtherClicks) {
        const excessClicks = otherClicks - maxOtherClicks;
        const remainingSegments = topData.length - 1;

        topData.forEach((item) => {
          if (item.country !== "Другое") {
            item.clicks += excessClicks / remainingSegments;
          }
        });
      }
    }

    return topData;
  };


  const pieData: ChartData<"pie"> = {
    labels: processPieData(data).map((d) => d.country),
    datasets: [
      {
        data: processPieData(data).map((d) => d.clicks),
        backgroundColor: [
          "#4285F4", // Google Blue
          "#DB4437", // Google Red
          "#F4B400", // Google Yellow
          "#0F9D58", // Google Green
          "#AB47BC", // Google Purple
          "#00ACC1", // Google Cyan
          "#FF5733", // Example additional color 1
          "#FFC300", // Example additional color 2
          "#DAF7A6", // Example additional color 3
          "#FF8C00", // Example additional color 4
          "#E67E22", // Example additional color 5
          "#2ECC71", // Example additional color 6
        ],
      },
    ],
  };


  const options: ChartOptions<"pie"> = {
    plugins: {
      tooltip: {
        enabled: false,
      },
      legend: {
        display: true, 
        position: "bottom", 
        labels: {
          boxWidth: 10, 
          padding: 10, 
          font: {
            size: 11,
            weight: "bold",
          },
          color: "#333",
        },
      },
      datalabels: {
        color: "#000",
        formatter: (value: number) => `${Math.floor(value)}`,
        anchor: "center",
        align: "center",
        offset: 0,
        textAlign: "center",
        padding: {
          top: 0,
          bottom: 0,
        },
        font: {
          size: 12,
        },
      },
    },
    layout: {
      padding: {
        top: 0,
        bottom: 0,
        left: 0, 
        right: 0, 
      },
    },
    cutout: "40%", 
    animation: {
      animateRotate: true, 
      duration: 20, 
      animateScale: false, 
    },
    responsive: true, 
    maintainAspectRatio: true, 
  };

  return (
    <div className="AddressCountryDev">
      <div className="AddHeader">
        <div className="CategoryDev">
          {categories.map((category, index) => (
            <div
              key={index}
              className={`CategoryItem ${
                currentIndex === index ? "selected" : ""
              }`}
              onClick={() => setCurrentIndex(index)}
            >
              {category.name}
            </div>
          ))}
        </div>
        <div className="FontSizeTextGPDev">
          <SortButtonAdd columns={columns} setSortOption={setSortOption} />
          <button
            className={`ToggleViewButton ${flag ? "active" : ""}`}
            onClick={() => setFlag(!flag)}
          >
            <svg
              width="20"
              height="20"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
              className={`ToggleViewIcon ${flag ? "active" : ""}`}
            >
              <path
                d="M21 10C21 6.13401 17.866 3 14 3V10H21Z"
                stroke={flag ? "#FFFFFF" : "#1C274C"}
                strokeWidth="1.8"
                strokeLinecap="round"
                strokeLinejoin="round"
              />
              <path
                d="M11 21C15.4183 21 19 17.4183 19 13H11V5C6.58172 5 3 8.58172 3 13C3 17.4183 6.58172 21 11 21Z"
                stroke={flag ? "#FFFFFF" : "#1C274C"}
                strokeWidth="1.8"
                strokeLinecap="round"
                strokeLinejoin="round"
              />
            </svg>
          </button>
        </div>
      </div>

      {isPro ? (
        <div
          style={{
            height: "300px",
            overflowY: "auto",
            overflowX: "hidden",
            marginTop: "25px",
            display: flag ? "flex" : "block",
            justifyContent: flag ? "center" : "initial",
            flexDirection: flag ? "column" : "initial",
            alignItems: flag ? "center" : "initial",
          }}
        >
          {flag ? (
            <Pie data={pieData} options={options} />
          ) : (
            data.map((value, index) => (
              <div key={index}>
                <MapGP
                  name={value.country}
                  clickCount={value.clicks}
                  SVG={"qwe"}
                  category={categories[currentIndex].name}
                  country_code={value.country_code}
                />
              </div>
            ))
          )}
        </div>
      ) : (
        <div className="blurred-container">
          <div className="blur-overlay"></div>
          <div className="access-icon">
            <svg
              version="1.1"
              id="Layer_1"
              x="0px"
              y="0px"
              viewBox="0 0 473.931 473.931"
              xmlSpace="preserve"
            >
              <circle
                style={{ fill: "#E84849" }}
                cx="236.966"
                cy="236.966"
                r="236.966"
              />
              <path
                style={{ fill: "#F4F5F5" }}
                d="M429.595,245.83c0,16.797-13.624,30.417-30.417,30.417H74.73c-16.797,0-30.421-13.62-30.421-30.417 v-17.743c0-16.797,13.624-30.417,30.421-30.417h324.448c16.793,0,30.417,13.62,30.417,30.417V245.83z"
              />
            </svg>
          </div>
          <div className="access-message">
            Для получения доступа к этому функционалу обновите тарифный план
          </div>
        </div>
      )}
    </div>
  );
};

export default AddresGp;
